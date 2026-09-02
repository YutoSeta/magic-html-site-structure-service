<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\InvalidSiteStructureException;
use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateSiteStructureRequest;
use App\Http\Requests\UpsertSiteStructureRequest;
use App\Http\Resources\SiteStructureResource;
use App\Models\SiteStructure;
use App\Services\Contracts\SiteStructureGenerator;
use App\Services\SiteStructureValidator;
use App\Support\CanonicalJson;
use App\Support\Problem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class SiteStructureController extends Controller
{
    public function show(Request $request, string $site): SiteStructureResource|JsonResponse
    {
        $structure = SiteStructure::query()->where('site_id', $site)->first();

        return $structure === null
            ? Problem::response($request, 404, 'site_structure_not_found', 'The site structure was not found.')
            : new SiteStructureResource($structure);
    }

    public function update(
        UpsertSiteStructureRequest $request,
        string $site,
        SiteStructureValidator $validator,
    ): JsonResponse {
        try {
            $structure = $validator->validate($request->validated('structure'));
        } catch (InvalidSiteStructureException $exception) {
            return Problem::response($request, 422, 'invalid_site_structure', $exception->getMessage());
        }

        return (new SiteStructureResource($this->persist($site, $structure, 'manual')))
            ->response()->setStatusCode(200);
    }

    public function generate(
        GenerateSiteStructureRequest $request,
        string $site,
        SiteStructureGenerator $generator,
    ): JsonResponse {
        $digest = hash('sha256', CanonicalJson::encode([
            'brief' => $request->validated('brief'),
            'locale' => $request->validated('locale'),
            'page_limit' => $request->integer('page_limit'),
            'execution_profile' => $request->validated('execution_profile'),
        ]));
        $existing = SiteStructure::query()
            ->where('site_id', $site)
            ->where('source', 'generated')
            ->where('brief_digest', $digest)
            ->first();
        if ($existing !== null) {
            return (new SiteStructureResource($existing))->response()->setStatusCode(200);
        }

        try {
            $structure = $generator->generate(
                $request->validated('brief'),
                $request->validated('locale'),
                $request->integer('page_limit'),
                $request->validated('execution_profile'),
            );
        } catch (InvalidSiteStructureException $exception) {
            return Problem::response($request, 422, 'invalid_site_structure', $exception->getMessage());
        } catch (RuntimeException $exception) {
            return Problem::response($request, 502, 'site_structure_provider_failed', $exception->getMessage());
        }

        return (new SiteStructureResource($this->persist($site, $structure, 'generated', $digest)))
            ->response()->setStatusCode(200);
    }

    public function destroy(Request $request, string $site): JsonResponse
    {
        $structure = SiteStructure::query()->where('site_id', $site)->first();
        if ($structure === null) {
            return Problem::response($request, 404, 'site_structure_not_found', 'The site structure was not found.');
        }
        $structure->delete();

        return response()->json(status: 204);
    }

    /** @param array<string,mixed> $structure */
    private function persist(string $site, array $structure, string $source, ?string $briefDigest = null): SiteStructure
    {
        return DB::transaction(function () use ($site, $structure, $source, $briefDigest): SiteStructure {
            $record = SiteStructure::query()->where('site_id', $site)->lockForUpdate()->first();
            if ($record === null) {
                return SiteStructure::query()->create([
                    'site_id' => $site,
                    'structure' => $structure,
                    'version' => 1,
                    'source' => $source,
                    'brief_digest' => $briefDigest,
                ]);
            }

            $record->update([
                'structure' => $structure,
                'version' => $record->version + 1,
                'source' => $source,
                'brief_digest' => $briefDigest,
            ]);

            return $record->refresh();
        });
    }
}
