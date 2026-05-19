<?php

namespace Tests\Feature;

use App\Models\KnowledgeDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KnowledgeDocumentDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_original_name_and_extension_must_be_unique(): void
    {
        KnowledgeDocument::create([
            'title' => 'Certificado antigo',
            'original_name' => 'certificado.pdf',
            'stored_path' => 'knowledge/documents/old.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size_bytes' => 1000,
            'status' => 'ready',
        ]);

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);

        KnowledgeDocument::create([
            'title' => 'Certificado novo',
            'original_name' => 'certificado.pdf',
            'stored_path' => 'knowledge/documents/new.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size_bytes' => 2000,
            'status' => 'indexing',
        ]);
    }
}
