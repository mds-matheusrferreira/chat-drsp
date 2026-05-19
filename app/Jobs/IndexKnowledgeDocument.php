<?php

namespace App\Jobs;

use App\Models\KnowledgeDocument;
use App\Services\Knowledge\KnowledgeIngestionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class IndexKnowledgeDocument implements ShouldQueue
{
    use Queueable;

    public int $timeout = 1800;

    public function __construct(public int $documentId) {}

    public function handle(KnowledgeIngestionService $ingestion): void
    {
        $document = KnowledgeDocument::find($this->documentId);

        if (! $document) {
            return;
        }

        $ingestion->index($document);
    }
}
