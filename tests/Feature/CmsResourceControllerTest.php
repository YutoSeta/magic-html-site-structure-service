<?php

namespace Tests\Feature;

use App\Models\CmsResource;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

final class CmsResourceControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('content.service_token', 'test-service-token');
    }

    public function test_put_resource_without_bearer_token_returns_401(): void
    {
        $this->putJson('/api/v1/sites/site-one/contents/about', $this->resource())
            ->assertUnauthorized();

        $this->assertDatabaseEmpty('cms_resources');
    }

    public function test_put_valid_resource_persists_site_scoped_draft(): void
    {
        $response = $this->withToken('test-service-token')
            ->putJson('/api/v1/sites/site-one/contents/about', $this->resource());

        $response->assertOk()
            ->assertJsonPath('site_id', 'site-one')
            ->assertJsonPath('type', 'contents')
            ->assertJsonPath('resource_key', 'about')
            ->assertJsonPath('value.title', 'About us');
        $this->assertDatabaseHas('cms_resources', [
            'site_id' => 'site-one',
            'type' => 'contents',
            'resource_key' => 'about',
        ]);
    }

    public function test_put_value_that_violates_json_schema_returns_422(): void
    {
        $payload = $this->resource();
        $payload['value']['title'] = 42;

        $this->withToken('test-service-token')
            ->putJson('/api/v1/sites/site-one/contents/about', $payload)
            ->assertUnprocessable()
            ->assertJsonPath('type', 'invalid_resource_value');

        $this->assertDatabaseEmpty('cms_resources');
    }

    public function test_put_resource_with_unknown_top_level_field_returns_422(): void
    {
        $payload = $this->resource();
        $payload['published'] = true;

        $this->withToken('test-service-token')
            ->putJson('/api/v1/sites/site-one/contents/about', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('published');

        $this->assertDatabaseEmpty('cms_resources');
    }

    public function test_same_resource_key_is_isolated_between_sites(): void
    {
        CmsResource::factory()->create([
            'site_id' => 'site-two',
            'type' => 'contents',
            'resource_key' => 'about',
            'value' => ['title' => 'Other tenant'],
        ]);

        $this->withToken('test-service-token')
            ->putJson('/api/v1/sites/site-one/contents/about', $this->resource())
            ->assertOk();

        $this->assertSame(2, CmsResource::query()->where('resource_key', 'about')->count());
        $this->assertDatabaseHas('cms_resources', [
            'site_id' => 'site-two',
            'resource_key' => 'about',
        ]);
    }

    public function test_empty_json_schema_accepts_any_json_value(): void
    {
        $payload = $this->resource();
        $payload['schema'] = (object) [];
        $payload['value'] = ['free_form' => ['value', 42, true]];

        $this->withToken('test-service-token')
            ->call(
                'PUT',
                '/api/v1/sites/site-one/contents/free-form',
                [],
                [],
                [],
                [
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_ACCEPT' => 'application/json',
                    'HTTP_AUTHORIZATION' => 'Bearer test-service-token',
                ],
                json_encode($payload, JSON_THROW_ON_ERROR),
            )
            ->assertOk()
            ->assertJsonPath('value.free_form.1', 42);
    }

    public function test_json_array_is_not_accepted_as_a_schema_object(): void
    {
        $payload = $this->resource();
        $payload['schema'] = [];

        $this->withToken('test-service-token')
            ->call(
                'PUT',
                '/api/v1/sites/site-one/contents/about',
                [],
                [],
                [],
                [
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_ACCEPT' => 'application/json',
                    'HTTP_AUTHORIZATION' => 'Bearer test-service-token',
                ],
                json_encode($payload, JSON_THROW_ON_ERROR),
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('schema');
    }

    /** @return array<string,mixed> */
    private function resource(): array
    {
        return [
            'contract_version' => '1.0',
            'name' => 'About',
            'schema' => [
                'type' => 'object',
                'required' => ['title'],
                'additionalProperties' => false,
                'properties' => ['title' => ['type' => 'string']],
            ],
            'value' => ['title' => 'About us'],
            'media_refs' => [],
        ];
    }
}
