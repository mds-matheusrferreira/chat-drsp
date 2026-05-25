<?php

namespace App\Services\Knowledge;

use App\Models\KnowledgeDocument;
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

        $results = $this->includeAdjacentChunks($results);

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

        return $this->filterExistingDocuments($payload['results'] ?? []);
    }

    private function includeAdjacentChunks(array $results): array
    {
        $expanded = collect($results);
        $documentChunks = $expanded
            ->map(function (array $result) {
                $metadata = $result['metadata'] ?? [];

                return [
                    'document_id' => $metadata['document_id'] ?? null,
                    'chunk_index' => isset($metadata['chunk_index']) ? (int) $metadata['chunk_index'] : null,
                ];
            })
            ->filter(fn (array $item) => $item['document_id'] !== null && $item['chunk_index'] !== null)
            ->take(3)
            ->values();

        if ($documentChunks->isEmpty()) {
            return $results;
        }

        $documents = KnowledgeDocument::query()
            ->whereIn('id', $documentChunks->pluck('document_id')->unique()->all())
            ->get()
            ->keyBy(fn (KnowledgeDocument $document) => (string) $document->id);

        foreach ($documentChunks as $item) {
            $document = $documents->get((string) $item['document_id']);

            if (! $document) {
                continue;
            }

            foreach ([$item['chunk_index'] - 1, $item['chunk_index'] + 1, $item['chunk_index'] + 2] as $chunkIndex) {
                if ($chunkIndex < 0) {
                    continue;
                }

                $expanded->push([
                    'content' => $this->chunkContent($document, $chunkIndex),
                    'title' => $document->title,
                    'original_name' => $document->original_name,
                    'metadata' => [
                        'document_id' => (string) $document->id,
                        'extension' => $document->extension,
                        'chunk_index' => $chunkIndex,
                    ],
                ]);
            }
        }

        return $expanded
            ->filter(fn (array $result) => trim($result['content'] ?? '') !== '')
            ->unique(fn (array $result) => ($result['metadata']['document_id'] ?? '').'-'.($result['metadata']['chunk_index'] ?? ''))
            ->take(12)
            ->values()
            ->all();
    }

    private function chunkContent(KnowledgeDocument $document, int $chunkIndex): string
    {
        $path = storage_path('app/private/'.$document->stored_path);

        if (! is_file($path)) {
            $path = storage_path('app/'.$document->stored_path);
        }

        if (! is_file($path)) {
            return '';
        }

        $content = file_get_contents($path);

        if ($content === false || trim($content) === '') {
            return '';
        }

        $normalized = collect(preg_split('/\R/', $content) ?: [])
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->implode("\n");

        $chunkSize = (int) config('knowledge.chunk_size', 700);
        $chunkOverlap = (int) config('knowledge.chunk_overlap', 120);
        $step = max(1, $chunkSize - $chunkOverlap);
        $start = $chunkIndex * $step;

        return trim(mb_substr($normalized, $start, $chunkSize));
    }

    private function filterExistingDocuments(array $results): array
    {
        $documentIds = collect($results)
            ->map(fn (array $result) => $result['metadata']['document_id'] ?? null)
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        if ($documentIds->isEmpty()) {
            return [];
        }

        $existingIds = KnowledgeDocument::query()
            ->whereIn('id', $documentIds->all())
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        return collect($results)
            ->filter(fn (array $result) => in_array((string) ($result['metadata']['document_id'] ?? ''), $existingIds, true))
            ->values()
            ->all();
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
