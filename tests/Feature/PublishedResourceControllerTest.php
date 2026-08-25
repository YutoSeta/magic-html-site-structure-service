<?php

namespace Tests\Feature;

use App\Models\Snapshot;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

final class PublishedResourceControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_returns_latest_published_content_without_a_bearer_token(): void
    {
        Snapshot::factory()->create([
            'site_id' => 'demo-site',
            'document' => [
                'contents' => ['home' => ['value' => ['title' => 'Published title']]],
                'collections' => [],
                'media' => [],
            ],
        ]);

        $this->getJson('/api/v1/sites/demo-site/published/contents/home', ['Origin' => 'https://static.example'])
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', '*')
            ->assertJsonPath('contract_version', '1.0')
            ->assertJsonPath('site_id', 'demo-site')
            ->assertJsonPath('type', 'contents')
            ->assertJsonPath('resource_key', 'home')
            ->assertJsonPath('data.title', 'Published title');
    }

    public function test_filters_and_paginates_a_published_collection(): void
    {
        Snapshot::factory()->create([
            'site_id' => 'demo-site',
            'document' => [
                'contents' => [],
                'collections' => [
                    'posts' => ['value' => ['items' => [
                        ['id' => 1, 'title' => 'Laravel Cloud'],
                        ['id' => 2, 'title' => 'Design AST'],
                        ['id' => 3, 'title' => 'Laravel Forms'],
                    ]]],
                ],
                'media' => [],
            ],
        ]);

        $this->getJson('/api/v1/sites/demo-site/published/collections/posts?q=laravel&per_page=1&page=2')
            ->assertOk()
            ->assertJsonPath('data.0.id', 3)
            ->assertJsonPath('meta.page', 2)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_returns_404_without_leaking_another_sites_published_resource(): void
    {
        Snapshot::factory()->create([
            'site_id' => 'private-site',
            'document' => [
                'contents' => ['home' => ['value' => ['title' => 'Private']]],
                'collections' => [],
                'media' => [],
            ],
        ]);

        $this->getJson('/api/v1/sites/public-site/published/contents/home')
            ->assertNotFound()
            ->assertJsonPath('type', 'published_resource_not_found');
    }

    public function test_options_returns_public_cors_headers(): void
    {
        $this->call('OPTIONS', '/api/v1/sites/demo-site/published/contents/home')
            ->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', '*')
            ->assertHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
    }
}
