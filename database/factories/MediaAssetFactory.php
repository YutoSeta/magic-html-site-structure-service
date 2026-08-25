<?php

namespace Database\Factories;

use App\Models\CmsResource;
use App\Models\MediaAsset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaAsset>
 */
final class MediaAssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cms_resource_id' => CmsResource::factory()->state(['type' => 'media']),
            'site_id' => 'site-test',
            'media_key' => (string) fake()->uuid(),
            'object_key' => 'sites/site-test/media/'.fake()->uuid().'.png',
            'original_name' => 'image.png',
            'mime' => 'image/png',
            'bytes' => 100,
            'width' => 10,
            'height' => 10,
            'sha256' => str_repeat('a', 64),
            'published_at' => null,
        ];
    }
}
