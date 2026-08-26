<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublishedResourceRequest;
use App\Models\Snapshot;
use App\Support\Problem;
use Illuminate\Http\JsonResponse;

final class PublishedResourceController extends Controller
{
    public function __invoke(PublishedResourceRequest $request, string $site, string $resource): JsonResponse
    {
        $snapshot = Snapshot::query()
            ->select(['site_id', 'version', 'document'])
            ->where('site_id', $site)
            ->latest('sequence')
            ->first();
        $publishedResource = $snapshot?->document['contents'][$resource] ?? null;

        if (! is_array($publishedResource) || ! array_key_exists('value', $publishedResource)) {
            return Problem::response($request, 404, 'published_resource_not_found', 'The published resource does not exist for this site.');
        }

        return response()->json($this->baseResponse($snapshot->version, $site, $resource) + [
            'data' => $publishedResource['value'],
        ]);
    }

    /** @return array<string,string> */
    private function baseResponse(string $version, string $site, string $resource): array
    {
        return [
            'contract_version' => '1.0',
            'site_id' => $site,
            'version' => $version,
            'type' => 'contents',
            'resource_key' => $resource,
        ];
    }
}
