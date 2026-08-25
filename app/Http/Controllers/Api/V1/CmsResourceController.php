<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\UpsertCmsResource;
use App\Exceptions\InvalidResourceSchemaException;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpsertCmsResourceRequest;
use App\Http\Resources\CmsResourceResource;
use App\Support\Problem;
use Illuminate\Http\JsonResponse;
use JsonException;

final class CmsResourceController extends Controller
{
    public function update(
        UpsertCmsResourceRequest $request,
        string $site,
        string $type,
        string $resource,
        UpsertCmsResource $upsert,
    ): JsonResponse {
        try {
            $document = json_decode($request->getContent(), false, 512, JSON_THROW_ON_ERROR);
            $record = $upsert->execute($site, $type, $resource, $request->validated(), $document->schema);
        } catch (InvalidResourceSchemaException $exception) {
            return Problem::response($request, 422, 'invalid_resource_value', $exception->getMessage(), $exception->errors);
        } catch (JsonException) {
            return Problem::response($request, 422, 'invalid_json', 'The request body is not valid JSON.');
        }

        return (new CmsResourceResource($record))->response()->setStatusCode(200);
    }
}
