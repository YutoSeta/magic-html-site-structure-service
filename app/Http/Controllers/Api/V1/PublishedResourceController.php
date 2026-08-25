<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublishedResourceRequest;
use App\Models\Snapshot;
use App\Support\Problem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

final class PublishedResourceController extends Controller
{
    public function __invoke(PublishedResourceRequest $request, string $site, string $type, string $resource): JsonResponse
    {
        $snapshot = Snapshot::query()
            ->select(['site_id', 'version', 'document'])
            ->where('site_id', $site)
            ->latest('sequence')
            ->first();
        $publishedResource = $snapshot?->document[$type][$resource] ?? null;

        if (! is_array($publishedResource) || ! array_key_exists('value', $publishedResource)) {
            return Problem::response($request, 404, 'published_resource_not_found', 'The published resource does not exist for this site.');
        }

        if ($type === 'contents') {
            return response()->json($this->baseResponse($snapshot->version, $site, $type, $resource) + [
                'data' => $publishedResource['value'],
            ]);
        }

        $items = $this->collectionItems($publishedResource['value']);
        if ($items === null) {
            return Problem::response($request, 422, 'published_collection_invalid', 'A published collection value must be a list or contain an items list.');
        }

        $query = $request->string('q')->trim()->lower()->toString();
        if ($query !== '') {
            $items = array_values(array_filter($items, fn (mixed $item): bool => Str::contains(
                Str::lower((string) json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
                $query,
            )));
        }

        $page = $request->integer('page', 1);
        $perPage = $request->integer('per_page', 12);
        $total = count($items);
        $lastPage = max(1, (int) ceil($total / $perPage));

        return response()->json($this->baseResponse($snapshot->version, $site, $type, $resource) + [
            'data' => array_slice($items, ($page - 1) * $perPage, $perPage),
            'meta' => [
                'page' => $page,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total' => $total,
            ],
        ]);
    }

    /** @return array<string,string> */
    private function baseResponse(string $version, string $site, string $type, string $resource): array
    {
        return [
            'contract_version' => '1.0',
            'site_id' => $site,
            'version' => $version,
            'type' => $type,
            'resource_key' => $resource,
        ];
    }

    /** @return list<mixed>|null */
    private function collectionItems(mixed $value): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        if (array_is_list($value)) {
            return $value;
        }

        $items = $value['items'] ?? null;

        return is_array($items) && array_is_list($items) ? $items : null;
    }
}
