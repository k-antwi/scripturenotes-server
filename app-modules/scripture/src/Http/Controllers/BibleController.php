<?php

namespace Nucleus\Scripture\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Nucleus\Scripture\Bible\BibleCache;
use Nucleus\Scripture\Bible\BibleProviderRouter;
use Nucleus\Scripture\Bible\Exceptions\ProviderUnavailableException;
use Nucleus\Scripture\Bible\Exceptions\QuotaExceededException;
use Nucleus\Scripture\Bible\Providers\BollsProvider;
use Nucleus\Scripture\Bible\Providers\YouVersionProvider;
use Nucleus\Scripture\Bible\ResponseNormalizer;

/**
 * BibleController — unified Bible API gateway (7 endpoints).
 *
 * All verse text is served through this controller.
 * The Vue / Capacitor frontend makes no direct calls to external Bible APIs.
 *
 * GET /api/bible/passage       ?ref=John+3:16&version=NIV
 * GET /api/bible/chapter       ?book=JHN&chapter=3&version=NIV
 * GET /api/bible/search        ?q=faith+hope+love&version=ESV&type=keyword|semantic
 * GET /api/bible/audio         ?ref=Psalm+23&version=KJV
 * GET /api/bible/verse-of-day
 * GET /api/bible/dictionary    ?word=grace
 * GET /api/bible/versions      ?language=en
 */
class BibleController extends Controller
{
    public function __construct(
        private readonly BibleProviderRouter $router,
        private readonly BibleCache $cache,
        private readonly ResponseNormalizer $normalizer,
        private readonly BollsProvider $bolls,
        private readonly YouVersionProvider $youVersion,
    ) {}

    // ─── GET /api/bible/passage ───────────────────────────────────────────────

    public function passage(Request $request): JsonResponse
    {
        $request->validate([
            'ref'     => ['required', 'string', 'max:100'],
            'version' => ['sometimes', 'string', 'max:20'],
        ]);

        $ref     = $request->input('ref');
        $version = strtoupper($request->input('version', 'KJV'));

        $cached = $this->cache->getPassage($ref, $version);
        if ($cached !== null) {
            data_set($cached, 'meta.cached', true);

            return response()->json($cached);
        }

        try {
            $provider = $this->router->forPassage($version);
            $raw      = $provider->getPassage($ref, $version);
            $result   = $this->normalizer->fromApiBible($raw, $version, $provider->name());

            $this->cache->putPassage($ref, $version, $result);
        } catch (QuotaExceededException) {
            $result = $this->router->withFallback('text', fn ($p) => $p->getPassage($ref, $version));
            $result = $this->normalizer->fromApiBible($result, $version, 'fallback');
        } catch (ProviderUnavailableException $e) {
            return response()->json(['error' => 'Bible API unavailable.', 'detail' => $e->getMessage()], 502);
        }

        return response()->json($result);
    }

    // ─── GET /api/bible/chapter ───────────────────────────────────────────────

    public function chapter(Request $request): JsonResponse
    {
        $request->validate([
            'book'    => ['required', 'string', 'max:10'],
            'chapter' => ['required', 'integer', 'min:1'],
            'version' => ['sometimes', 'string', 'max:20'],
        ]);

        $book    = strtoupper($request->input('book'));
        $chapter = (int) $request->input('chapter');
        $version = strtoupper($request->input('version', 'KJV'));

        $cached = $this->cache->getChapter($book, $chapter, $version);
        if ($cached !== null) {
            data_set($cached, 'meta.cached', true);

            return response()->json($cached);
        }

        try {
            $provider = $this->router->forPassage($version);
            $raw      = $provider->getChapter($book, $chapter, $version);
            $result   = $this->normalizer->fromApiBible($raw, $version, $provider->name());

            $this->cache->putChapter($book, $chapter, $version, $result);
        } catch (QuotaExceededException) {
            $result = $this->router->withFallback('text', fn ($p) => $p->getChapter($book, $chapter, $version));
            $result = $this->normalizer->fromApiBible($result, $version, 'fallback');
        } catch (ProviderUnavailableException $e) {
            return response()->json(['error' => 'Bible API unavailable.', 'detail' => $e->getMessage()], 502);
        }

        return response()->json($result);
    }

    // ─── GET /api/bible/search ────────────────────────────────────────────────

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q'       => ['required', 'string', 'min:2', 'max:200'],
            'version' => ['sometimes', 'string', 'max:20'],
            'type'    => ['sometimes', 'in:keyword,semantic'],
        ]);

        $query   = $request->input('q');
        $version = strtoupper($request->input('version', 'KJV'));
        $type    = $request->input('type', 'keyword');

        $cached = $this->cache->getSearch($query, $version, $type);
        if ($cached !== null) {
            data_set($cached, 'meta.cached', true);

            return response()->json($cached);
        }

        try {
            if ($type === 'semantic') {
                $raw    = $this->bolls->search($query, $version);
                $result = $this->normalizer->fromBollsSearch($raw, $version);
            } else {
                $provider = $this->router->forPassage($version);
                $raw      = $provider->search($query, $version);
                $result   = $this->normalizer->fromApiBible($raw, $version, $provider->name());
            }

            $this->cache->putSearch($query, $version, $type, $result);
        } catch (ProviderUnavailableException $e) {
            return response()->json(['error' => 'Search unavailable.', 'detail' => $e->getMessage()], 502);
        }

        return response()->json($result);
    }

    // ─── GET /api/bible/audio ─────────────────────────────────────────────────

    public function audio(Request $request): JsonResponse
    {
        $request->validate([
            'ref'     => ['required', 'string', 'max:100'],
            'version' => ['sometimes', 'string', 'max:20'],
        ]);

        $ref     = $request->input('ref');
        $version = strtoupper($request->input('version', 'KJV'));

        $cached = $this->cache->getAudio($ref, $version);
        if ($cached !== null) {
            data_set($cached, 'meta.cached', true);

            return response()->json($cached);
        }

        try {
            $provider = $this->router->forCapability('audio');
            // BibleBrainProvider exposes getAudio() beyond the interface
            /** @var \Nucleus\Scripture\Bible\Providers\BibleBrainProvider $provider */
            $raw    = $provider->getAudio($ref, $version);
            $result = $this->normalizer->fromBibleBrainAudio($raw, $ref, $version);

            $this->cache->putAudio($ref, $version, $result);
        } catch (ProviderUnavailableException $e) {
            return response()->json(['error' => 'Audio unavailable.', 'detail' => $e->getMessage()], 502);
        }

        return response()->json($result);
    }

    // ─── GET /api/bible/verse-of-day ─────────────────────────────────────────

    public function verseOfDay(): JsonResponse
    {
        $date = now()->toDateString();

        $cached = $this->cache->getVotd($date);
        if ($cached !== null) {
            data_set($cached, 'meta.cached', true);

            return response()->json($cached);
        }

        try {
            $result = $this->router->withFallback('verse_of_day', function ($provider) {
                /** @var YouVersionProvider $provider */
                return $provider instanceof YouVersionProvider
                    ? $provider->getVerseOfDay()
                    : $provider->getPassage('John 3:16', 'KJV');
            });

            $result = $this->normalizer->fromYouVersionVotd($result);
            $this->cache->putVotd($result, $date);
        } catch (ProviderUnavailableException $e) {
            return response()->json(['error' => 'Verse of Day unavailable.', 'detail' => $e->getMessage()], 502);
        }

        return response()->json($result);
    }

    // ─── GET /api/bible/dictionary ────────────────────────────────────────────

    public function dictionary(Request $request): JsonResponse
    {
        $request->validate([
            'word' => ['required', 'string', 'min:1', 'max:100'],
        ]);

        $word = strtolower($request->input('word'));

        $cached = $this->cache->getDictionary($word);
        if ($cached !== null) {
            return response()->json(array_merge($cached, ['meta' => ['cached' => true]]));
        }

        try {
            $raw    = $this->bolls->getDictionary($word);
            $result = [
                'data' => ['word' => $word, 'definition' => $raw],
                'meta' => ['provider' => 'bolls', 'cached' => false],
            ];

            $this->cache->putDictionary($word, $result);
        } catch (ProviderUnavailableException $e) {
            return response()->json(['error' => 'Dictionary unavailable.', 'detail' => $e->getMessage()], 502);
        }

        return response()->json($result);
    }

    // ─── GET /api/bible/versions ──────────────────────────────────────────────

    public function versions(Request $request): JsonResponse
    {
        $language = $request->input('language', 'en');

        $cached = $this->cache->getVersions($language);
        if ($cached !== null) {
            data_set($cached, 'meta.cached', true);

            return response()->json($cached);
        }

        try {
            // Merge results from API.Bible (licensed) and FreeUse (public-domain)
            $apiBibleVersions  = $this->router->forCapability('text') instanceof \Nucleus\Scripture\Bible\Providers\ApiBibleProvider
                ? $this->router->forPassage('NIV')->getVersions($language)
                : [];

            $freeUseVersions   = $this->router->forPassage('KJV')->getVersions($language);

            $result = $this->normalizer->fromVersionsList(
                array_merge_recursive($apiBibleVersions, $freeUseVersions),
                'combined',
            );

            $this->cache->putVersions($language, $result);
        } catch (ProviderUnavailableException $e) {
            return response()->json(['error' => 'Versions list unavailable.', 'detail' => $e->getMessage()], 502);
        }

        return response()->json($result);
    }
}
