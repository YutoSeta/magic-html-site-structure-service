<?php

namespace Tests\Feature;

use App\Models\MediaAsset;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class MediaControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('cms.service_token', 'test-service-token');
        config()->set('cms.media_disk', 's3');
        Storage::fake('s3');
    }

    public function test_png_can_be_uploaded_as_site_scoped_media(): void
    {
        $response = $this->withToken('test-service-token')
            ->post('/api/v1/sites/site-one/media', [
                'file' => UploadedFile::fake()->image('hero.png', 1200, 630),
                'name' => 'Hero image',
            ], ['Accept' => 'application/json']);

        $response->assertCreated()
            ->assertJsonPath('site_id', 'site-one')
            ->assertJsonPath('name', 'Hero image')
            ->assertJsonPath('mime', 'image/png')
            ->assertJsonPath('width', 1200)
            ->assertJsonPath('height', 630)
            ->assertJsonPath('public_url', null);
        $this->assertMatchesRegularExpression('/^[a-f0-9-]{36}$/', (string) $response->json('media_key'));
        $this->assertDatabaseHas('cms_resources', [
            'site_id' => 'site-one',
            'type' => 'media',
            'resource_key' => $response->json('media_key'),
        ]);
        $asset = MediaAsset::query()->sole();
        Storage::disk('s3')->assertExists($asset->object_key);
    }

    public function test_disallowed_file_type_is_rejected_without_storage_or_database_changes(): void
    {
        $this->withToken('test-service-token')
            ->post('/api/v1/sites/site-one/media', [
                'file' => UploadedFile::fake()->create('payload.svg', 1, 'image/svg+xml'),
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');

        $this->assertDatabaseEmpty('media_assets');
        $this->assertDatabaseEmpty('cms_resources');
        $this->assertSame([], Storage::disk('s3')->allFiles());
    }

    public function test_preview_requires_authentication_and_public_file_requires_publish(): void
    {
        $upload = $this->withToken('test-service-token')
            ->post('/api/v1/sites/site-one/media', [
                'file' => UploadedFile::fake()->image('thumbnail.png', 320, 180),
            ], ['Accept' => 'application/json'])
            ->assertCreated();
        $key = $upload->json('media_key');

        $this->withHeader('Authorization', '')
            ->getJson("/api/v1/sites/site-one/media/{$key}/file")
            ->assertUnauthorized();
        $this->get("/media/site-one/{$key}")
            ->assertNotFound();

        $this->withToken('test-service-token')
            ->get("/api/v1/sites/site-one/media/{$key}/file")
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('Cache-Control', 'no-store, private');

        $this->withToken('test-service-token')
            ->postJson('/api/v1/sites/site-one/snapshots')
            ->assertCreated();

        $this->get("/media/site-one/{$key}")
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('Cache-Control', 'immutable, max-age=31536000, public');
    }

    public function test_media_cannot_be_read_through_another_site(): void
    {
        $upload = $this->withToken('test-service-token')
            ->post('/api/v1/sites/site-one/media', [
                'file' => UploadedFile::fake()->image('hero.png'),
            ], ['Accept' => 'application/json'])
            ->assertCreated();
        $key = $upload->json('media_key');

        $this->withToken('test-service-token')
            ->getJson("/api/v1/sites/site-two/media/{$key}/file")
            ->assertNotFound()
            ->assertJsonPath('type', 'media_not_found');

        $this->get("/media/site-two/{$key}")
            ->assertNotFound();
    }
}
