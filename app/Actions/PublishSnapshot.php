<?php

namespace App\Actions;

use App\Models\CmsResource;
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
                ->where('type', 'contents')
                ->orderBy('resource_key')
                ->lockForUpdate()
                ->get();
            $document = ['contents' => []];
            foreach ($resources as $resource) {
                $document['contents'][$resource->resource_key] = [
                    'name' => $resource->name,
                    'schema' => $resource->schema,
                    'value' => $resource->value,
                    'media_refs' => $resource->media_refs,
                ];
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

            return $snapshot;
        });
    }
}
