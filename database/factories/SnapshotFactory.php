<?php

namespace Database\Factories;

use App\Models\Snapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Snapshot>
 */
final class SnapshotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_id' => 'site-test',
            'sequence' => 1,
            'version' => 'v1-'.str_repeat('a', 12),
            'digest' => str_repeat('a', 64),
            'document' => ['contents' => [], 'collections' => [], 'media' => []],
            'published_at' => now(),
        ];
    }
}
