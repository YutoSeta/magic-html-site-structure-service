<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class MediaAssetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'contract_version' => '1.0',
            'id' => $this->id,
            'site_id' => $this->site_id,
            'media_key' => $this->media_key,
            'name' => $this->cmsResource->name,
            'original_name' => $this->original_name,
            'mime' => $this->mime,
            'bytes' => $this->bytes,
            'width' => $this->width,
            'height' => $this->height,
            'sha256' => $this->sha256,
            'preview_url' => url("/api/v1/sites/{$this->site_id}/media/{$this->media_key}/file"),
            'public_url' => $this->published_at === null ? null : url("/media/{$this->site_id}/{$this->media_key}"),
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
