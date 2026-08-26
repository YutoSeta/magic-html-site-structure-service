<?php

namespace Database\Factories;

use App\Models\SiteStructure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteStructure>
 */
final class SiteStructureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_id' => fake()->unique()->slug(2),
            'structure' => [
                'version' => 1,
                'site' => ['name' => fake()->company(), 'description' => fake()->sentence()],
                'pages' => [['key' => 'home', 'path' => '/', 'title' => 'Home', 'purpose' => 'Top page']],
                'navigation' => [['label' => 'Home', 'path' => '/']],
            ],
            'version' => 1,
            'source' => 'manual',
            'brief_digest' => null,
        ];
    }
}
