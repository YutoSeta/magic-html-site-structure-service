<?php

namespace Database\Factories;

use App\Models\CmsResource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CmsResource>
 */
final class CmsResourceFactory extends Factory
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
            'type' => 'contents',
            'resource_key' => fake()->slug(),
            'name' => fake()->words(3, true),
            'schema' => [
                'type' => 'object',
                'required' => ['title'],
                'properties' => ['title' => ['type' => 'string']],
            ],
            'value' => ['title' => fake()->sentence()],
            'media_refs' => [],
        ];
    }
}
