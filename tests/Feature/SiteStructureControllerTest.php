<?php

namespace Tests\Feature;

use App\Services\Contracts\SiteStructureGenerator;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

final class SiteStructureControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('site_structure.service_token', 'test-token');
    }

    public function test_manual_structure_crud_is_site_scoped_and_versioned(): void
    {
        $this->withToken('test-token')->putJson('/api/v1/sites/site-one/structure', [
            'contract_version' => '1.0',
            'structure' => $this->structure('First'),
        ])->assertOk()->assertJsonPath('version', 1)->assertJsonPath('structure.site.name', 'First');

        $this->withToken('test-token')->putJson('/api/v1/sites/site-one/structure', [
            'contract_version' => '1.0',
            'structure' => $this->structure('Second'),
        ])->assertOk()->assertJsonPath('version', 2);

        $this->withToken('test-token')->getJson('/api/v1/sites/site-one/structure')
            ->assertOk()->assertJsonPath('structure.site.name', 'Second');
        $this->withToken('test-token')->getJson('/api/v1/sites/site-two/structure')->assertNotFound();
        $this->withToken('test-token')->deleteJson('/api/v1/sites/site-one/structure')->assertNoContent();
    }

    public function test_invalid_structure_and_missing_authentication_are_rejected(): void
    {
        $this->putJson('/api/v1/sites/site-one/structure', [
            'contract_version' => '1.0',
            'structure' => $this->structure('First'),
        ])->assertUnauthorized();

        $structure = $this->structure('First');
        $structure['pages'][0]['path'] = '/not-home';
        $this->withToken('test-token')->putJson('/api/v1/sites/site-one/structure', [
            'contract_version' => '1.0',
            'structure' => $structure,
        ])->assertUnprocessable()->assertJsonPath('type', 'invalid_site_structure');
    }

    public function test_generation_uses_the_generator_boundary_and_persists_result(): void
    {
        $this->app->instance(SiteStructureGenerator::class, new class implements SiteStructureGenerator
        {
            public function generate(array $brief, string $locale, int $pageLimit): array
            {
                return [
                    'version' => 1,
                    'site' => ['name' => $brief['organization'], 'description' => 'Generated'],
                    'pages' => [['key' => 'home', 'path' => '/', 'title' => 'Home', 'purpose' => 'Top']],
                    'navigation' => [['label' => 'Home', 'path' => '/']],
                ];
            }
        });

        $this->withToken('test-token')->postJson('/api/v1/sites/site-one/structure/generate', [
            'contract_version' => '1.0',
            'brief' => [
                'organization' => 'Web工房',
                'goals' => '問い合わせ増加',
                'audience' => '中小企業',
                'tone' => '誠実',
                'requirements' => '5ページ',
                'materials' => [],
            ],
            'locale' => 'ja',
            'page_limit' => 5,
        ])->assertOk()
            ->assertJsonPath('source', 'generated')
            ->assertJsonPath('structure.site.name', 'Web工房');

        $this->assertDatabaseHas('site_structures', ['site_id' => 'site-one', 'source' => 'generated']);
    }

    /** @return array<string,mixed> */
    private function structure(string $name): array
    {
        return [
            'site' => ['name' => $name, 'description' => 'Description'],
            'pages' => [['key' => 'home', 'path' => '/', 'title' => 'Home', 'purpose' => 'Top']],
            'navigation' => [['label' => 'Home', 'path' => '/']],
        ];
    }
}
