<?php

namespace Tests\Feature;

use App\Models\CmsResource;
use App\Models\Snapshot;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

final class SnapshotControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('content.service_token', 'test-service-token');
    }

    public function test_post_snapshot_groups_drafts_and_returns_201(): void
    {
        CmsResource::factory()->create([
            'site_id' => 'site-one',
            'type' => 'contents',
            'resource_key' => 'home',
            'value' => ['title' => 'Home'],
        ]);
        $response = $this->withToken('test-service-token')
            ->postJson('/api/v1/sites/site-one/snapshots');

        $response->assertCreated()
            ->assertJsonPath('site_id', 'site-one')
            ->assertJsonPath('contents.home.value.title', 'Home');
        $this->assertMatchesRegularExpression('/^v1-[a-f0-9]{12}$/', (string) $response->json('version'));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', (string) $response->json('digest'));
        $this->assertDatabaseCount('snapshots', 1);
    }

    public function test_published_snapshot_remains_immutable_after_draft_changes(): void
    {
        $resource = CmsResource::factory()->create([
            'site_id' => 'site-one',
            'type' => 'contents',
            'resource_key' => 'home',
            'value' => ['title' => 'Version one'],
        ]);
        $published = $this->withToken('test-service-token')
            ->postJson('/api/v1/sites/site-one/snapshots')
            ->assertCreated();
        $resource->update(['value' => ['title' => 'Version two']]);

        $this->withToken('test-service-token')
            ->getJson('/api/v1/sites/site-one/snapshots/'.$published->json('version'))
            ->assertOk()
            ->assertJsonPath('contents.home.value.title', 'Version one');
    }

    public function test_get_snapshot_through_another_site_returns_404(): void
    {
        $snapshot = Snapshot::factory()->create(['site_id' => 'site-one']);

        $this->withToken('test-service-token')
            ->getJson('/api/v1/sites/site-two/snapshots/'.$snapshot->version)
            ->assertNotFound()
            ->assertJsonPath('type', 'snapshot_not_found');
    }

    public function test_post_snapshot_without_bearer_token_returns_401(): void
    {
        $this->postJson('/api/v1/sites/site-one/snapshots')
            ->assertUnauthorized();

        $this->assertDatabaseEmpty('snapshots');
    }
}
