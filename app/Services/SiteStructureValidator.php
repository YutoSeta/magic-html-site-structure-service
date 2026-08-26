<?php

namespace App\Services;

use App\Exceptions\InvalidSiteStructureException;

final class SiteStructureValidator
{
    /** @param array<string,mixed> $document @return array<string,mixed> */
    public function validate(array $document, int $pageLimit = 20): array
    {
        $site = $document['site'] ?? null;
        $pages = $document['pages'] ?? null;
        $navigation = $document['navigation'] ?? null;
        $this->assert(is_array($site) && is_array($pages) && is_array($navigation), 'Site structure must contain site, pages and navigation.');
        $this->assert(array_is_list($pages) && count($pages) >= 1 && count($pages) <= $pageLimit, 'Site structure has an invalid page count.');

        $normalizedPages = [];
        $keys = [];
        $paths = [];
        foreach ($pages as $index => $page) {
            $this->assert(is_array($page), 'Every page must be an object.');
            $key = $this->text($page['key'] ?? null, 100, 'Page key');
            $path = $this->text($page['path'] ?? null, 200, 'Page path');
            $this->assert((bool) preg_match('/^[a-z0-9][a-z0-9-]*$/', $key), 'Page keys must use lowercase URL-safe characters.');
            $this->assert($path === '/' || (bool) preg_match('#^/[a-z0-9][a-z0-9/-]*$#', $path), 'Page paths must be lowercase internal paths.');
            $path = $path === '/' ? '/' : '/'.trim($path, '/');
            $this->assert(! isset($keys[$key]) && ! isset($paths[$path]), 'Page keys and paths must be unique.');
            if ($index === 0) {
                $this->assert($key === 'home' && $path === '/', 'The first page must be home at /.');
            }
            $keys[$key] = true;
            $paths[$path] = true;
            $normalizedPages[] = [
                'key' => $key,
                'path' => $path,
                'title' => $this->text($page['title'] ?? null, 200, 'Page title'),
                'purpose' => $this->text($page['purpose'] ?? null, 1000, 'Page purpose'),
            ];
        }

        $normalizedNavigation = [];
        $seenNavigation = [];
        foreach ($navigation as $item) {
            $this->assert(is_array($item), 'Every navigation item must be an object.');
            $path = $this->text($item['path'] ?? null, 200, 'Navigation path');
            $path = $path === '/' ? '/' : '/'.trim($path, '/');
            $this->assert(isset($paths[$path]), 'Navigation may only reference generated pages.');
            $this->assert(! isset($seenNavigation[$path]), 'Navigation paths must be unique.');
            $seenNavigation[$path] = true;
            $normalizedNavigation[] = [
                'label' => $this->text($item['label'] ?? null, 100, 'Navigation label'),
                'path' => $path,
            ];
        }

        return [
            'version' => 1,
            'site' => [
                'name' => $this->text($site['name'] ?? null, 200, 'Site name'),
                'description' => $this->text($site['description'] ?? null, 1000, 'Site description'),
            ],
            'pages' => $normalizedPages,
            'navigation' => $normalizedNavigation,
        ];
    }

    private function text(mixed $value, int $max, string $label): string
    {
        $this->assert(is_string($value), "{$label} must be text.");
        $value = trim($value);
        $this->assert($value !== '' && mb_strlen($value) <= $max, "{$label} is empty or too long.");

        return $value;
    }

    private function assert(bool $condition, string $message): void
    {
        if (! $condition) {
            throw new InvalidSiteStructureException($message);
        }
    }
}
