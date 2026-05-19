<?php

namespace Tests\Feature;

use App\Jobs\IndexKnowledgeDocument;
use App\Models\KnowledgeDocument;
use App\Services\Knowledge\KnowledgeIngestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class KnowledgeDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_dispatches_indexing_job_and_creates_indexing_document(): void
    {
        Bus::fake();
        Storage::fake('local');

        $file = UploadedFile::fake()->create('certificado.pdf', 120, 'application/pdf');

        $document = app(KnowledgeIngestionService::class)->ingest($file, 'Certificado SUAS');

        $this->assertDatabaseHas('knowledge_documents', [
            'id' => $document->id,
            'title' => 'Certificado SUAS',
            'original_name' => 'certificado.pdf',
            'extension' => 'pdf',
            'status' => 'indexing',
        ]);

        Storage::assertExists($document->stored_path);
        Bus::assertDispatched(IndexKnowledgeDocument::class, fn (IndexKnowledgeDocument $job) => $job->documentId === $document->id);
    }

    public function test_upload_with_same_original_name_and_extension_replaces_old_document(): void
    {
        Bus::fake();
        Storage::fake('local');

        $ingestion = app(KnowledgeIngestionService::class);

        $old = $ingestion->ingest(UploadedFile::fake()->create('certificado.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'), 'Certificado antigo');
        $oldPath = $old->stored_path;

        $new = $ingestion->ingest(UploadedFile::fake()->create('certificado.xlsx', 150, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'), 'Certificado novo');

        $this->assertDatabaseMissing('knowledge_documents', ['id' => $old->id]);
        $this->assertDatabaseHas('knowledge_documents', [
            'id' => $new->id,
            'title' => 'Certificado novo',
            'original_name' => 'certificado.xlsx',
            'extension' => 'xlsx',
        ]);
        $this->assertSame(1, KnowledgeDocument::where('original_name', 'certificado.xlsx')->where('extension', 'xlsx')->count());
        Storage::assertMissing($oldPath);
    }

    public function test_selected_documents_are_deleted_only_with_admin_password(): void
    {
        config(['knowledge.document_admin_password' => 'drsp']);

        $document = KnowledgeDocument::create([
            'title' => 'Certificado',
            'original_name' => 'certificado.pdf',
            'stored_path' => 'knowledge/documents/certificado.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size_bytes' => 1000,
            'status' => 'ready',
        ]);

        $ingestion = Mockery::mock(KnowledgeIngestionService::class);
        $ingestion->shouldReceive('delete')->once()->with(Mockery::on(fn (KnowledgeDocument $deleted) => $deleted->is($document)));
        $this->app->instance(KnowledgeIngestionService::class, $ingestion);

        $this->withSession(['documents_admin_authenticated' => true])
            ->post(route('documents.destroy-selected'), [
                'documents' => [$document->id],
                'password' => 'drsp',
            ])
            ->assertRedirect(route('documents.index'))
            ->assertSessionHas('status', 'Documentos selecionados removidos.');
    }

    public function test_selected_documents_are_not_deleted_with_wrong_password(): void
    {
        config(['knowledge.document_admin_password' => 'drsp']);

        $document = KnowledgeDocument::create([
            'title' => 'Certificado',
            'original_name' => 'certificado.pdf',
            'stored_path' => 'knowledge/documents/certificado.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size_bytes' => 1000,
            'status' => 'ready',
        ]);

        $ingestion = Mockery::mock(KnowledgeIngestionService::class);
        $ingestion->shouldNotReceive('delete');
        $this->app->instance(KnowledgeIngestionService::class, $ingestion);

        $this->withSession(['documents_admin_authenticated' => true])
            ->post(route('documents.destroy-selected'), [
                'documents' => [$document->id],
                'password' => 'errada',
            ])
            ->assertSessionHasErrors('password');

        $this->assertDatabaseHas('knowledge_documents', ['id' => $document->id]);
    }
}
