<?php

namespace App\Services\Knowledge;


class KnowledgeSearchService
{
    public function __construct(private KnowledgePythonProcess $python)
    {
    }

    public function contextFor(string $query): string
    {
        $results = $this->search($query);

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
