<?php

namespace App\Services\Knowledge;

use Illuminate\Support\Str;


class KnowledgeSearchService
{
    public function __construct(private KnowledgePythonProcess $python)
    {
    }

    public function contextFor(string $query): string
    {
        return $this->contextFromResults($this->search($query));
    }

    public function contextFromResults(array $results): string
    {
        if ($results === []) {
            return '';
        }

        return collect($results)
            ->map(function (array $result, int $index) {
                $title = $result['title'] ?: ($result['original_name'] ?? 'Documento interno');
                $content = trim($result['content'] ?? '');

                return "[Trecho ".($index + 1)." — {$title}]\n{$content}";
            })
            ->implode("\n\n");
    }

    public function sourcesFromResults(array $results): array
    {
        return collect($results)
            ->filter(fn (array $result) => trim($result['content'] ?? '') !== '')
            ->take((int) config('knowledge.search_limit', 8))
            ->values()
            ->map(function (array $result, int $index) {
                $metadata = $result['metadata'] ?? [];
                $content = trim(preg_replace('/\s+/', ' ', $result['content'] ?? ''));

                return [
                    'excerpt_number' => $index + 1,
                    'title' => $result['title'] ?: ($result['original_name'] ?? 'Documento interno'),
                    'original_name' => $result['original_name'] ?? null,
                    'extension' => $metadata['extension'] ?? null,
                    'chunk_index' => isset($metadata['chunk_index']) ? (int) $metadata['chunk_index'] : null,
                    'excerpt' => Str::limit($content, 360),
                ];
            })
            ->all();
    }

    public function search(string $query): array
    {
        $script = base_path('knowledge/search.py');
        try {
            $result = $this->python->run([
                config('knowledge.python_bin'),
                $script,
                '--query='.$query,
                '--limit='.(string) config('knowledge.search_limit'),
            ], 120);
        } catch (\Throwable) {
            return [];
        }

        if ($result->failed()) {
            return [];
        }

        $payload = $this->decodePayload($result->output());

        if (! is_array($payload)) {
            return [];
        }

        return $payload['results'] ?? [];
    }

    private function decodePayload(string $output): ?array
    {
        $payload = json_decode(trim($output), true);

        if (is_array($payload)) {
            return $payload;
        }

        foreach (array_reverse(preg_split('/\R/', $output) ?: []) as $line) {
            $line = trim($line);

            if (! str_starts_with($line, '{')) {
                continue;
            }

            $payload = json_decode($line, true);

            if (is_array($payload)) {
                return $payload;
            }
        }

        return null;
    }
}
