<?php

namespace App\Actions;

use App\Exceptions\MissingMediaReferenceException;
use App\Models\CmsResource;
use App\Models\MediaAsset;
use App\Models\Snapshot;
use App\Support\CanonicalJson;
use Illuminate\Support\Facades\DB;

final class PublishSnapshot
{
    public function execute(string $siteId): Snapshot
    {
        return DB::transaction(function () use ($siteId): Snapshot {
            $resources = CmsResource::query()
                ->where('site_id', $siteId)
                ->orderBy('type')
                ->orderBy('resource_key')
                ->lockForUpdate()
                ->get();
            $document = ['contents' => [], 'collections' => [], 'media' => []];
            foreach ($resources as $resource) {
                $document[$resource->type][$resource->resource_key] = [
                    'name' => $resource->name,
                    'schema' => $resource->schema,
                    'value' => $resource->value,
                    'media_refs' => $resource->media_refs,
                ];
            }

            $mediaKeys = array_keys($document['media']);
            $missing = [];
            foreach (['contents', 'collections'] as $type) {
                foreach ($document[$type] as $resource) {
                    foreach ($resource['media_refs'] as $mediaRef) {
                        if (! in_array($mediaRef, $mediaKeys, true)) {
                            $missing[] = $mediaRef;
                        }
                    }
                }
            }
            if ($missing !== []) {
                throw new MissingMediaReferenceException(array_values(array_unique($missing)));
            }

            $document = json_decode(CanonicalJson::encode($document), true, 512, JSON_THROW_ON_ERROR);
            $digest = hash('sha256', CanonicalJson::encode(['site_id' => $siteId, ...$document]));
            $sequence = ((int) Snapshot::query()->where('site_id', $siteId)->max('sequence')) + 1;
            $snapshot = Snapshot::query()->create([
                'site_id' => $siteId,
                'sequence' => $sequence,
                'version' => "v{$sequence}-".substr($digest, 0, 12),
                'digest' => $digest,
                'document' => $document,
                'published_at' => now(),
            ]);
            MediaAsset::query()
                ->where('site_id', $siteId)
                ->whereIn('media_key', $mediaKeys)
                ->whereNull('published_at')
                ->update(['published_at' => $snapshot->published_at]);

            return $snapshot;
        });
    }
}
