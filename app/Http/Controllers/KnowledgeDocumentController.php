<?php

namespace App\Http\Controllers;

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
        ]);
    }

    public function store(Request $request, KnowledgeIngestionService $ingestion)
    {
        $extensions = implode(',', config('knowledge.allowed_extensions'));
        $maxKilobytes = config('knowledge.max_upload_mb') * 1024;

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'document' => ['required', 'file', 'max:'.$maxKilobytes, 'mimes:'.$extensions],
        ]);

        $document = $ingestion->ingest($data['document'], $data['title'] ?? null);

        $message = 'Documento recebido. A indexação continuará em segundo plano; acompanhe o status na lista.';

        return redirect()->route('documents.index')->with('status', $message);
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
