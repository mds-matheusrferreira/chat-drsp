<?php

namespace App\Http\Controllers;

use App\Jobs\IndexKnowledgeDocument;
use App\Models\KnowledgeDocument;
use App\Services\Knowledge\KnowledgeIngestionService;
use Illuminate\Http\Request;

class KnowledgeDocumentController extends Controller
{
    public function index()
    {
        return view('documents.index', [
            'documents' => KnowledgeDocument::latest()->get(),
            'allowedExtensions' => config('knowledge.allowed_extensions'),
            'maxUploadMb' => config('knowledge.max_upload_mb'),
        ]);
    }

    public function status()
    {
        return response()->json([
            'documents' => KnowledgeDocument::latest()->get()->map(fn (KnowledgeDocument $document) => [
                'id' => $document->id,
                'title' => $document->title,
                'original_name' => $document->original_name,
                'extension' => $document->extension,
                'status' => $document->status,
                'chunks_count' => $document->chunks_count,
                'error_message' => $document->error_message,
                'updated_at' => $document->updated_at?->toIso8601String(),
            ]),
        ]);
    }

    public function store(Request $request, KnowledgeIngestionService $ingestion)
    {
        $extensions = implode(',', config('knowledge.allowed_extensions'));
        $maxKilobytes = config('knowledge.max_upload_mb') * 1024;

        $data = $request->validate([
            'documents' => ['required', 'array', 'min:1'],
            'documents.*' => ['required', 'file', 'max:'.$maxKilobytes, 'mimes:'.$extensions],
        ]);

        foreach ($data['documents'] as $file) {
            $ingestion->ingest($file);
        }

        $count = count($data['documents']);
        $message = $count === 1
            ? 'Documento recebido. A indexação continuará em segundo plano; acompanhe o status na lista.'
            : "{$count} documentos recebidos. A indexação continuará em segundo plano; acompanhe o status na lista.";

        return redirect()->route('documents.index')->with('status', $message);
    }

    public function storeText(Request $request, KnowledgeIngestionService $ingestion)
    {
        $data = $request->validate([
            'manual_title' => ['required', 'string', 'max:255'],
            'manual_text' => ['required', 'string', 'min:10', 'max:200000'],
        ]);

        if (trim($data['manual_text']) === '' || mb_strlen(trim($data['manual_text'])) < 10) {
            return back()
                ->withInput()
                ->withErrors(['manual_text' => 'Informe um texto com pelo menos 10 caracteres.']);
        }

        try {
            $ingestion->ingestText($data['manual_title'], $data['manual_text']);
        } catch (\InvalidArgumentException $exception) {
            return back()
                ->withInput()
                ->withErrors(['manual_title' => $exception->getMessage()]);
        }

        return redirect()
            ->route('documents.index')
            ->with('status', 'Texto recebido. A indexação continuará em segundo plano; acompanhe o status na lista.');
    }

    public function reprocess(KnowledgeDocument $document)
    {
        if ($document->status === 'indexing') {
            return redirect()->route('documents.index')->with('status', 'Documento já está em indexação.');
        }

        $document->update([
            'status' => 'indexing',
            'chunks_count' => 0,
            'error_message' => null,
        ]);

        IndexKnowledgeDocument::dispatch($document->id);

        return redirect()->route('documents.index')->with('status', 'Reprocessamento iniciado.');
    }

    public function destroySelected(Request $request, KnowledgeIngestionService $ingestion)
    {
        $data = $request->validate([
            'documents' => ['required', 'array', 'min:1'],
            'documents.*' => ['integer', 'exists:knowledge_documents,id'],
            'password' => ['required', 'string'],
        ]);

        if (! hash_equals((string) config('knowledge.document_admin_password'), $data['password'])) {
            return back()->withErrors(['password' => 'Senha inválida para excluir documentos.']);
        }

        KnowledgeDocument::query()
            ->whereIn('id', $data['documents'])
            ->get()
            ->each(fn (KnowledgeDocument $document) => $ingestion->delete($document));

        return redirect()->route('documents.index')->with('status', 'Documentos selecionados removidos.');
    }

    public function destroy(KnowledgeDocument $document, KnowledgeIngestionService $ingestion)
    {
        $ingestion->delete($document);

        return redirect()->route('documents.index')->with('status', 'Documento removido.');
    }
}
