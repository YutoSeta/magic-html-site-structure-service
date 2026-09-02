<?php

namespace App\Services;

use App\Exceptions\InvalidSiteStructureException;
use App\Services\Contracts\SiteStructureGenerator;
use App\Support\ExecutionProfile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class OpenAiSiteStructureGenerator implements SiteStructureGenerator
{
    public function __construct(
        private readonly SiteStructureValidator $validator,
        private readonly ExecutionProfile $executionProfiles,
    ) {}

    /** @param array<string,mixed> $brief @return array<string,mixed> */
    public function generate(array $brief, string $locale, int $pageLimit, string $executionProfile = 'fast'): array
    {
        $profile = $this->executionProfiles->resolve($executionProfile);
        $feedback = null;
        for ($attempt = 0; $attempt < 2; $attempt++) {
            $response = Http::withToken((string) config('services.openai.key'))
                ->acceptJson()
                ->timeout((int) config('services.openai.timeout', 300))
                ->retry([1000, 3000, 7000], throw: false)
                ->post((string) config('services.openai.url'), [
                    'model' => $profile['model'],
                    'instructions' => $this->instructions(),
                    'input' => json_encode([
                        'brief' => $brief,
                        'locale' => $locale,
                        'page_limit' => $pageLimit,
                        'validation_feedback' => $feedback,
                    ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    'reasoning' => ['effort' => $profile['reasoning_effort']],
                    'text' => ['format' => [
                        'type' => 'json_schema',
                        'name' => 'site_structure',
                        'strict' => true,
                        'schema' => $this->schema(),
                    ]],
                    'max_output_tokens' => 12000,
                    'store' => false,
                    'metadata' => ['stage' => 'site_structure', 'execution_profile' => $profile['id']],
                ]);

            if (! $response->successful()) {
                throw new RuntimeException('The site structure provider rejected the request.');
            }

            $text = $response->json('output_text');
            if (! is_string($text)) {
                $text = $this->outputText((array) $response->json('output', []));
            }
            $document = is_string($text) ? json_decode($text, true) : null;
            if (! is_array($document)) {
                throw new RuntimeException('The site structure provider returned invalid JSON.');
            }

            try {
                return $this->validator->validate($document, $pageLimit);
            } catch (InvalidSiteStructureException $exception) {
                $feedback = $exception->getMessage();
            }
        }

        throw new InvalidSiteStructureException('No valid site structure was produced. '.($feedback ?? ''));
    }

    private function instructions(): string
    {
        return 'You are a website information architect. Return a concise multi-page Site AST. Keys use lowercase ASCII letters, digits and hyphens. Paths are unique internal paths. The first page is home at /. Navigation only references generated pages. Do not generate HTML, CSS, content copy, image URLs, scripts, tracking code, or external links.';
    }

    /** @return array<string,mixed> */
    private function schema(): array
    {
        $text = fn (int $max): array => ['type' => 'string', 'maxLength' => $max];
        $object = fn (array $properties, array $required): array => [
            'type' => 'object',
            'properties' => $properties,
            'required' => $required,
            'additionalProperties' => false,
        ];

        return $object([
            'site' => $object(['name' => $text(200), 'description' => $text(1000)], ['name', 'description']),
            'pages' => ['type' => 'array', 'minItems' => 1, 'maxItems' => 20, 'items' => $object([
                'key' => $text(100), 'path' => $text(200), 'title' => $text(200), 'purpose' => $text(1000),
            ], ['key', 'path', 'title', 'purpose'])],
            'navigation' => ['type' => 'array', 'maxItems' => 20, 'items' => $object([
                'label' => $text(100), 'path' => $text(200),
            ], ['label', 'path'])],
        ], ['site', 'pages', 'navigation']);
    }

    /** @param array<int,mixed> $output */
    private function outputText(array $output): string
    {
        foreach ($output as $item) {
            foreach ((array) ($item['content'] ?? []) as $content) {
                if (($content['type'] ?? null) === 'output_text' && is_string($content['text'] ?? null)) {
                    return $content['text'];
                }
            }
        }

        return '';
    }
}
