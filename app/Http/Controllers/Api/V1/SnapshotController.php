<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\PublishSnapshot;
use App\Exceptions\MissingMediaReferenceException;
use App\Http\Controllers\Controller;
use App\Http\Resources\SnapshotResource;
use App\Models\Snapshot;
use App\Support\Problem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SnapshotController extends Controller
{
    public function store(Request $request, string $site, PublishSnapshot $publish): JsonResponse
    {
        try {
            $snapshot = $publish->execute($site);
        } catch (MissingMediaReferenceException $exception) {
            return Problem::response($request, 422, 'missing_media_reference', $exception->getMessage(), [
                'media_refs' => $exception->mediaRefs,
            ]);
        }

        return (new SnapshotResource($snapshot))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Request $request, string $site, string $version): SnapshotResource|JsonResponse
    {
        $snapshot = Snapshot::query()
            ->where('site_id', $site)
            ->where('version', $version)
            ->first();
        if ($snapshot === null) {
            return Problem::response($request, 404, 'snapshot_not_found', 'The snapshot does not exist for this site.');
        }

        return new SnapshotResource($snapshot);
    }
}
