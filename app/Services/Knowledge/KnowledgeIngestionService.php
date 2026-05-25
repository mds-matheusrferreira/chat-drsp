<?php

namespace App\Services\Knowledge;

use App\Jobs\IndexKnowledgeDocument;
use App\Models\KnowledgeDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KnowledgeIngestionService
{
    public function __construct(private KnowledgePythonProcess $python)
    {
    }

    public function ingest(UploadedFile $file, ?string $title = null): KnowledgeDocument
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $originalName = $file->getClientOriginalName();
        $filename = Str::uuid().'.'.$extension;
        $path = $file->storeAs(config('knowledge.document_path'), $filename);

        $document = DB::transaction(function () use ($file, $title, $extension, $originalName, $path): KnowledgeDocument {
            $this->replaceExisting($originalName, $extension);

            return KnowledgeDocument::create([
                'title' => $title ?: pathinfo($originalName, PATHINFO_FILENAME),
                'original_name' => $originalName,
                'stored_path' => $path,
                'mime_type' => $file->getMimeType(),
                'extension' => $extension,
                'size_bytes' => $file->getSize(),
                'status' => 'indexing',
            ]);
        });

        IndexKnowledgeDocument::dispatchSync($document->id);

        return $document->refresh();
    }

    public function ingestText(string $title, string $text): KnowledgeDocument
    {
        $title = trim($title);
        $text = trim($text);
        $slug = Str::slug($title);

        if ($slug === '') {
            throw new \InvalidArgumentException('Informe um título com letras ou números para identificar o texto.');
        }

        $extension = 'txt';
        $originalName = $slug.'.'.$extension;
        $path = config('knowledge.document_path').'/'.Str::uuid().'.'.$extension;

        Storage::put($path, $text.PHP_EOL);

        $document = DB::transaction(function () use ($title, $text, $extension, $originalName, $path): KnowledgeDocument {
            $this->replaceExisting($originalName, $extension);

            return KnowledgeDocument::create([
                'title' => $title,
                'original_name' => $originalName,
                'stored_path' => $path,
                'mime_type' => 'text/plain',
                'extension' => $extension,
                'size_bytes' => strlen($text),
                'status' => 'indexing',
            ]);
        });

        IndexKnowledgeDocument::dispatchSync($document->id);

        return $document->refresh();
    }

    public function delete(KnowledgeDocument $document): void
    {
        $script = base_path('knowledge/delete.py');

        $result = $this->python->run([
            config('knowledge.python_bin'),
            $script,
            '--document-id='.$document->id,
        ], 120);

        $output = trim($result->output()) ?: trim($result->errorOutput());
        $payload = $this->jsonPayload($output);

        if ($result->failed() || ! is_array($payload) || ($payload['status'] ?? null) !== 'deleted') {
            report(new \RuntimeException($payload['error'] ?? $output ?: 'Falha ao remover documento da base vetorial.'));
        }

        Storage::delete($document->stored_path);
        $document->delete();
    }

    public function index(KnowledgeDocument $document): void
    {
        $script = base_path('knowledge/ingest.py');
        $absolutePath = Storage::path($document->stored_path);

        $result = $this->python->run([
            config('knowledge.python_bin'),
            $script,
            '--document-id='.$document->id,
            '--path='.$absolutePath,
            '--title='.$document->title,
            '--original-name='.$document->original_name,
            '--chunk-size='.(string) config('knowledge.chunk_size'),
            '--chunk-overlap='.(string) config('knowledge.chunk_overlap'),
        ], 600);


        $output = trim($result->output()) ?: trim($result->errorOutput());
        $payload = $this->jsonPayload($output);

        if ($result->failed() || ! is_array($payload) || ($payload['status'] ?? null) !== 'ready') {
            $document->update([
                'status' => 'failed',
                'error_message' => $payload['error'] ?? $output ?: 'Falha ao indexar documento.',
            ]);

            return;
        }

        $document->update([
            'status' => 'ready',
            'chunks_count' => $payload['chunks_count'] ?? 0,
            'error_message' => null,
        ]);
    }

    private function jsonPayload(string $output): ?array
    {
        if ($output === '') {
            return null;
        }

        $payload = json_decode($output, true);

        if (is_array($payload)) {
            return $payload;
        }

        $lines = array_reverse(preg_split('/\R/', $output) ?: []);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || ! str_starts_with($line, '{')) {
                continue;
            }

            $payload = json_decode($line, true);

            if (is_array($payload)) {
                return $payload;
            }
        }

        return null;
    }

    private function replaceExisting(string $originalName, string $extension): void
    {
        KnowledgeDocument::query()
            ->where('original_name', $originalName)
            ->where('extension', $extension)
            ->lockForUpdate()
            ->get()
            ->each(fn (KnowledgeDocument $document) => $this->delete($document));
    }
}
