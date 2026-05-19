<?php

namespace App\Services\Knowledge;

use App\Jobs\IndexKnowledgeDocument;
use App\Models\KnowledgeDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KnowledgeIngestionService
{
    public function ingest(UploadedFile $file, ?string $title = null): KnowledgeDocument
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $originalName = $file->getClientOriginalName();
        $filename = Str::uuid().'.'.$extension;
        $path = $file->storeAs(config('knowledge.document_path'), $filename);

        $document = DB::transaction(function () use ($file, $title, $extension, $originalName, $path): KnowledgeDocument {
            KnowledgeDocument::query()
                ->where('original_name', $originalName)
                ->where('extension', $extension)
                ->lockForUpdate()
                ->get()
                ->each(fn (KnowledgeDocument $document) => $this->delete($document));

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

        IndexKnowledgeDocument::dispatch($document->id);

        return $document->refresh();
    }

    public function delete(KnowledgeDocument $document): void
    {
        $script = base_path('knowledge/delete.py');

        Process::path(base_path())
            ->env([
                'KNOWLEDGE_CHROMA_PATH' => config('knowledge.chroma_path'),
            ])
            ->timeout(120)
            ->run([
                config('knowledge.python_bin'),
                $script,
                '--document-id='.$document->id,
            ]);

        Storage::delete($document->stored_path);
        $document->delete();
    }

    public function index(KnowledgeDocument $document): void
    {
        $script = base_path('knowledge/ingest.py');
        $absolutePath = Storage::path($document->stored_path);

        $result = Process::path(base_path())
            ->env([
                'KNOWLEDGE_CHROMA_PATH' => config('knowledge.chroma_path'),
            ])
            ->timeout(600)
            ->run([
                config('knowledge.python_bin'),
                $script,
                '--document-id='.$document->id,
                '--path='.$absolutePath,
                '--title='.$document->title,
                '--original-name='.$document->original_name,
            ]);

        $output = trim($result->output()) ?: trim($result->errorOutput());
        $payload = json_decode($output, true);

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
}
