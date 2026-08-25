<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CmsResourceResource extends JsonResource
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
            'type' => $this->type,
            'resource_key' => $this->resource_key,
            'name' => $this->name,
            'schema' => $this->schema,
            'value' => $this->value,
            'media_refs' => $this->media_refs,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
