<?php

namespace App\Models;

use Database\Factories\SiteStructureFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class SiteStructure extends Model
{
    /** @use HasFactory<SiteStructureFactory> */
    use HasFactory;

    use HasUuids;

    /** @var list<string> */
    protected $fillable = ['site_id', 'structure', 'version', 'source', 'brief_digest'];

    /** @return array<string,string> */
    protected function casts(): array
    {
        return ['structure' => 'array', 'version' => 'integer'];
    }
}
