<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\StoreMedia;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMediaRequest;
use App\Http\Resources\MediaAssetResource;
use App\Models\MediaAsset;
use App\Support\Problem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class MediaController extends Controller
{
    public function store(StoreMediaRequest $request, string $site, StoreMedia $store): JsonResponse
    {
        $asset = $store->execute($site, $request->file('file'), $request->validated('name'));

        return (new MediaAssetResource($asset->load('cmsResource')))->response()->setStatusCode(201);
    }

    public function preview(Request $request, string $site, string $media): StreamedResponse|JsonResponse
    {
        return $this->stream($request, $site, $media, false);
    }

    public function published(Request $request, string $site, string $media): StreamedResponse|JsonResponse
    {
        return $this->stream($request, $site, $media, true);
    }

    private function stream(Request $request, string $site, string $media, bool $publishedOnly): StreamedResponse|JsonResponse
    {
        $asset = MediaAsset::query()
            ->where('site_id', $site)
            ->where('media_key', $media)
            ->when($publishedOnly, fn ($query) => $query->whereNotNull('published_at'))
            ->first();
        if ($asset === null) {
            return Problem::response($request, 404, 'media_not_found', 'The media asset does not exist.');
        }

        $disk = Storage::disk((string) config('cms.media_disk'));
        $stream = $disk->readStream($asset->object_key);
        if (! is_resource($stream)) {
            return Problem::response($request, 404, 'media_object_not_found', 'The media object is unavailable.');
        }

        return response()->stream(function () use ($stream): void {
            try {
                fpassthru($stream);
            } finally {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $asset->mime,
            'Content-Length' => (string) $asset->bytes,
            'ETag' => '"'.$asset->sha256.'"',
            'Cache-Control' => $publishedOnly ? 'public, max-age=31536000, immutable' : 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
