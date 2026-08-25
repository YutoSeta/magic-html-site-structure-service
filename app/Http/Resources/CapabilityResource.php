<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CapabilityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => 'magic-html-cms-service',
            'tier' => 1,
            'contract_version' => '1.0',
            'documentation' => url('/api/__verify'),
            'health' => url('/up'),
            'operations' => [
                'PUT /api/v1/sites/{site}/{type}/{resource}',
                'POST /api/v1/sites/{site}/snapshots',
                'GET /api/v1/sites/{site}/snapshots/{version}',
                'POST /api/v1/sites/{site}/media',
            ],
        ];
    }
}
