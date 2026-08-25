<?php

namespace App\Actions;

use App\Exceptions\InvalidResourceSchemaException;
use App\Models\CmsResource;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Validator;
use stdClass;
use Throwable;

final class UpsertCmsResource
{
    /** @param array<string,mixed> $attributes */
    public function execute(
        string $siteId,
        string $type,
        string $resourceKey,
        array $attributes,
        stdClass $schemaDocument,
    ): CmsResource {
        $this->validateValue($attributes['value'], $schemaDocument);
        unset($attributes['contract_version']);
        $attributes['site_id'] = $siteId;
        $attributes['type'] = $type;
        $attributes['resource_key'] = $resourceKey;
        $attributes['media_refs'] ??= [];

        return CmsResource::query()->updateOrCreate(
            ['site_id' => $siteId, 'type' => $type, 'resource_key' => $resourceKey],
            $attributes,
        );
    }

    private function validateValue(mixed $value, stdClass $schema): void
    {
        try {
            $dataObject = json_decode(json_encode($value, JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
            $result = (new Validator(null, 10, false))->validate($dataObject, $schema);
        } catch (Throwable $exception) {
            throw new InvalidResourceSchemaException(['schema' => [$exception->getMessage()]]);
        }
        if ($result->isValid()) {
            return;
        }

        $error = $result->error();
        throw new InvalidResourceSchemaException(
            $error === null ? ['value' => ['Schema validation failed.']] : (new ErrorFormatter)->format($error),
        );
    }
}
