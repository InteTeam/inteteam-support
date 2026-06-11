<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KbService
{
    public function __construct(
        private readonly UsageCounterService $usage,
    ) {}

    /**
     * Query inteteam-rag for article suggestions matching the given text.
     *
     * Returns an array of articles: [['title', 'url', 'excerpt'], ...].
     * Returns [] silently on any error (RAG is non-critical — ticket/chat must still work).
     * Only queries when query is at least 10 characters.
     */
    public function suggest(Tenant $tenant, string $query, int $limit = 3): array
    {
        $query = trim($query);
        if (mb_strlen($query) < 10) {
            return [];
        }

        $ragUrl = rtrim(config('services.inteteam_rag.url', env('RAG_URL', '')), '/');
        if ($ragUrl === '') {
            return [];
        }

        try {
            $response = Http::timeout(3)->post("{$ragUrl}/api/search", [
                'query' => $query,
                'limit' => $limit,
            ]);

            if (! $response->successful()) {
                return [];
            }

            $articles = $response->json('articles', []);

            // Only count a lookup when we actually got results back.
            if (! empty($articles)) {
                $this->usage->increment($tenant, 'kb_lookups');
            }

            return $articles;
        } catch (\Throwable $e) {
            Log::warning('inteteam-rag unavailable', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
