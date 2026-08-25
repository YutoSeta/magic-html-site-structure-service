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
            ->assertJsonPath('name', 'magic-html-cms-service')
            ->assertJsonPath('tier', 1)
            ->assertJsonPath('contract_version', '1.0');
    }

    public function test_runtime_verification_reports_migrated_database(): void
    {
        config()->set('cms.media_disk', 'local');

        $this->getJson('/api/__verify')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('checks.contract_installed', true)
            ->assertJsonPath('checks.database', true)
            ->assertJsonPath('checks.media_disk', 'local');
    }
}
