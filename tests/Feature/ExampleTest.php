<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

final class ExampleTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_capability_document_describes_the_service(): void
    {
        $this->getJson('/api')
            ->assertOk()
            ->assertJsonPath('name', 'magic-html-content-service')
            ->assertJsonPath('tier', 1)
            ->assertJsonPath('contract_version', '1.0');
    }

    public function test_runtime_verification_reports_migrated_database(): void
    {
        $this->getJson('/api/__verify')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('checks.contract_installed', true)
            ->assertJsonPath('checks.database', true);
    }

    public function test_service_does_not_expose_collection_or_media_routes(): void
    {
        $this->getJson('/api/v1/sites/site-one/published/collections/posts')->assertNotFound();
        $this->postJson('/api/v1/sites/site-one/media')->assertNotFound();
    }
}
