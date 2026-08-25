<?php

namespace App\Models;

use Database\Factories\SnapshotFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Snapshot extends Model
{
    /** @use HasFactory<SnapshotFactory> */
    use HasFactory;

    use HasUuids;

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'sequence',
        'version',
        'digest',
        'document',
        'published_at',
    ];

    /** @return array<string,string> */
    protected function casts(): array
    {
        return [
            'document' => 'array',
            'published_at' => 'immutable_datetime',
        ];
    }
}
