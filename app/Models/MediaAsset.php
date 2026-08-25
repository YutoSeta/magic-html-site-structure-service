<?php

namespace App\Models;

use Database\Factories\MediaAssetFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MediaAsset extends Model
{
    /** @use HasFactory<MediaAssetFactory> */
    use HasFactory;

    use HasUuids;

    /** @var list<string> */
    protected $fillable = [
        'cms_resource_id',
        'site_id',
        'media_key',
        'object_key',
        'original_name',
        'mime',
        'bytes',
        'width',
        'height',
        'sha256',
        'published_at',
    ];

    /** @return BelongsTo<CmsResource, $this> */
    public function cmsResource(): BelongsTo
    {
        return $this->belongsTo(CmsResource::class);
    }

    /** @return array<string,string> */
    protected function casts(): array
    {
        return ['published_at' => 'immutable_datetime'];
    }
}
