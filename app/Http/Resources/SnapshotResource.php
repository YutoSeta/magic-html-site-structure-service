<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class SnapshotResource extends JsonResource
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
            'site_id' => $this->site_id,
            'version' => $this->version,
            'published_at' => $this->published_at->toIso8601String(),
            'digest' => $this->digest,
            'contents' => $this->document['contents'],
            'collections' => $this->document['collections'],
            'media' => $this->document['media'],
        ];
    }
}
