<?php

namespace App\Actions;

use App\Models\CmsResource;
use App\Models\MediaAsset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final class StoreMedia
{
    public function execute(string $siteId, UploadedFile $file, ?string $name = null): MediaAsset
    {
        $mediaKey = (string) Str::uuid();
        $mime = (string) ($file->getMimeType() ?: 'application/octet-stream');
        $filename = $mediaKey.'.'.$this->extension($mime);
        $directory = "sites/{$siteId}/media";
        $disk = (string) config('cms.media_disk', 's3');
        $stored = Storage::disk($disk)->putFileAs($directory, $file, $filename);
        if (! is_string($stored)) {
            throw new RuntimeException('The media object could not be stored.');
        }

        $dimensions = str_starts_with($mime, 'image/') ? @getimagesize($file->getRealPath()) : false;
        $sha256 = hash_file('sha256', $file->getRealPath());
        try {
            return DB::transaction(function () use ($siteId, $file, $name, $mediaKey, $stored, $mime, $dimensions, $sha256): MediaAsset {
                $displayName = $name ?? $file->getClientOriginalName();
                $value = [
                    'url' => "/media/{$siteId}/{$mediaKey}",
                    'mime' => $mime,
                    'bytes' => (int) $file->getSize(),
                    'width' => is_array($dimensions) ? (int) $dimensions[0] : null,
                    'height' => is_array($dimensions) ? (int) $dimensions[1] : null,
                    'sha256' => $sha256,
                ];
                $resource = CmsResource::query()->create([
                    'site_id' => $siteId,
                    'type' => 'media',
                    'resource_key' => $mediaKey,
                    'name' => str($displayName)->limit(255, ''),
                    'schema' => [
                        'type' => 'object',
                        'required' => ['url', 'mime', 'bytes', 'sha256'],
                        'properties' => [
                            'url' => ['type' => 'string'],
                            'mime' => ['type' => 'string'],
                            'bytes' => ['type' => 'integer'],
                            'width' => ['type' => ['integer', 'null']],
                            'height' => ['type' => ['integer', 'null']],
                            'sha256' => ['type' => 'string'],
                        ],
                    ],
                    'value' => $value,
                    'media_refs' => [],
                ]);

                return MediaAsset::query()->create([
                    'cms_resource_id' => $resource->id,
                    'site_id' => $siteId,
                    'media_key' => $mediaKey,
                    'object_key' => $stored,
                    'original_name' => str($file->getClientOriginalName())->limit(255, ''),
                    'mime' => $mime,
                    'bytes' => (int) $file->getSize(),
                    'width' => is_array($dimensions) ? (int) $dimensions[0] : null,
                    'height' => is_array($dimensions) ? (int) $dimensions[1] : null,
                    'sha256' => $sha256,
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($stored);
            throw $exception;
        }
    }

    private function extension(string $mime): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/avif' => 'avif',
            'application/pdf' => 'pdf',
            'video/mp4' => 'mp4',
            'video/webm' => 'webm',
            'audio/mpeg' => 'mp3',
            'audio/wav' => 'wav',
            'audio/ogg' => 'ogg',
            default => 'bin',
        };
    }
}
