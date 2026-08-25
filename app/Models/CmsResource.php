<?php

namespace App\Models;

use Database\Factories\CmsResourceFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class CmsResource extends Model
{
    /** @use HasFactory<CmsResourceFactory> */
    use HasFactory;

    use HasUuids;

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'type',
        'resource_key',
        'name',
        'schema',
        'value',
        'media_refs',
    ];

    /** @return HasOne<MediaAsset, $this> */
    public function mediaAsset(): HasOne
    {
        return $this->hasOne(MediaAsset::class);
    }

    /** @return array<string,string> */
    protected function casts(): array
    {
        return [
            'schema' => 'array',
            'value' => 'json',
            'media_refs' => 'array',
        ];
    }
}
