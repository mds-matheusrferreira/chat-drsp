<?php

namespace App\Services\Knowledge;

use Illuminate\Support\Facades\Process;

class KnowledgeSearchService
{
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

        $result = Process::path(base_path())
            ->env([
                'KNOWLEDGE_CHROMA_PATH' => config('knowledge.chroma_path'),
            ])
            ->timeout(120)
            ->run([
                config('knowledge.python_bin'),
                $script,
                '--query='.$query,
                '--limit='.(string) config('knowledge.search_limit'),
            ]);

        if ($result->failed()) {
            return [];
        }

        $payload = json_decode(trim($result->output()), true);

        if (! is_array($payload)) {
            return [];
        }

        return $payload['results'] ?? [];
    }
}
