<?php

namespace Tests\Feature;

use App\Jobs\IndexKnowledgeDocument;
use App\Models\KnowledgeDocument;
use App\Services\Knowledge\KnowledgeIngestionService;
use App\Services\Knowledge\KnowledgePythonProcess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Process;
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

    public function test_index_passes_chunking_configuration_to_python(): void
    {
        config([
            'knowledge.chunk_size' => 700,
            'knowledge.chunk_overlap' => 120,
            'knowledge.tmp_path' => storage_path('framework/testing/knowledge-tmp'),
        ]);

        Storage::fake('local');
        Storage::put('knowledge/documents/certificado.pdf', 'conteudo');
        Process::fake([
            '*' => Process::result(output: json_encode([
                'status' => 'ready',
                'chunks_count' => 4,
                'characters_count' => 1800,
                'chunk_size' => 700,
                'chunk_overlap' => 120,
            ])),
        ]);

        $document = KnowledgeDocument::create([
            'title' => 'Certificado',
            'original_name' => 'certificado.pdf',
            'stored_path' => 'knowledge/documents/certificado.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size_bytes' => 1000,
            'status' => 'indexing',
        ]);

        app(KnowledgeIngestionService::class)->index($document);

        $expectedTempPath = storage_path('framework/testing/knowledge-tmp');
        $environment = app(KnowledgePythonProcess::class)->environment();

        $this->assertSame($expectedTempPath, $environment['TMP']);
        $this->assertSame($expectedTempPath, $environment['TEMP']);
        $this->assertSame($expectedTempPath, $environment['TMPDIR']);

        $this->assertDatabaseHas('knowledge_documents', [
            'id' => $document->id,
            'status' => 'ready',
            'chunks_count' => 4,
        ]);
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

    public function test_manual_text_creates_txt_document_and_dispatches_indexing_job(): void
    {
        Bus::fake();
        Storage::fake('local');

        $document = app(KnowledgeIngestionService::class)->ingestText(
            'Fluxo de atendimento SUAS',
            'Este texto descreve o fluxo interno de atendimento do SUAS para entidades privadas.'
        );

        $this->assertDatabaseHas('knowledge_documents', [
            'id' => $document->id,
            'title' => 'Fluxo de atendimento SUAS',
            'original_name' => 'fluxo-de-atendimento-suas.txt',
            'mime_type' => 'text/plain',
            'extension' => 'txt',
            'status' => 'indexing',
        ]);

        Storage::assertExists($document->stored_path);
        $this->assertStringContainsString('fluxo interno de atendimento', Storage::get($document->stored_path));
        Bus::assertDispatched(IndexKnowledgeDocument::class, fn (IndexKnowledgeDocument $job) => $job->documentId === $document->id);
    }

    public function test_manual_text_with_same_title_replaces_previous_text(): void
    {
        Bus::fake();
        Storage::fake('local');

        $ingestion = app(KnowledgeIngestionService::class);
        $old = $ingestion->ingestText('Fluxo SUAS', 'Texto antigo sobre o fluxo SUAS.');
        $oldPath = $old->stored_path;
        $new = $ingestion->ingestText('Fluxo SUAS', 'Texto novo sobre o fluxo SUAS atualizado.');

        $this->assertDatabaseMissing('knowledge_documents', ['id' => $old->id]);
        $this->assertDatabaseHas('knowledge_documents', [
            'id' => $new->id,
            'title' => 'Fluxo SUAS',
            'original_name' => 'fluxo-suas.txt',
            'extension' => 'txt',
        ]);
        $this->assertSame(1, KnowledgeDocument::where('original_name', 'fluxo-suas.txt')->where('extension', 'txt')->count());
        Storage::assertMissing($oldPath);
        $this->assertStringContainsString('Texto novo', Storage::get($new->stored_path));
    }

    public function test_delete_removes_database_and_storage_when_chroma_delete_fails(): void
    {
        Storage::fake('local');
        Storage::put('knowledge/documents/certificado.pdf', 'conteudo');
        Process::fake([
            '*' => Process::result(output: json_encode([
                'status' => 'failed',
                'error' => 'Chroma indisponível',
            ]), exitCode: 1),
        ]);

        $document = KnowledgeDocument::create([
            'title' => 'Certificado',
            'original_name' => 'certificado.pdf',
            'stored_path' => 'knowledge/documents/certificado.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size_bytes' => 1000,
            'status' => 'ready',
        ]);

        app(KnowledgeIngestionService::class)->delete($document);

        $this->assertDatabaseMissing('knowledge_documents', ['id' => $document->id]);
        Storage::assertMissing('knowledge/documents/certificado.pdf');
    }

    public function test_delete_removes_database_and_storage_after_chroma_delete_succeeds(): void
    {
        Storage::fake('local');
        Storage::put('knowledge/documents/certificado.pdf', 'conteudo');
        Process::fake([
            '*' => Process::result(output: json_encode(['status' => 'deleted'])),
        ]);

        $document = KnowledgeDocument::create([
            'title' => 'Certificado',
            'original_name' => 'certificado.pdf',
            'stored_path' => 'knowledge/documents/certificado.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size_bytes' => 1000,
            'status' => 'ready',
        ]);

        app(KnowledgeIngestionService::class)->delete($document);

        $this->assertDatabaseMissing('knowledge_documents', ['id' => $document->id]);
        Storage::assertMissing('knowledge/documents/certificado.pdf');
    }

    public function test_documents_status_endpoint_returns_document_statuses(): void
    {
        $this->withSession(['documents_admin_authenticated' => true]);

        $document = KnowledgeDocument::create([
            'title' => 'CEBAS',
            'original_name' => 'cebas.txt',
            'stored_path' => 'knowledge/documents/cebas.txt',
            'mime_type' => 'text/plain',
            'extension' => 'txt',
            'size_bytes' => 1000,
            'status' => 'failed',
            'chunks_count' => 0,
            'error_message' => 'Falha ao extrair texto.',
        ]);

        $response = $this->getJson(route('documents.status'));

        $response->assertOk()
            ->assertJsonPath('documents.0.id', $document->id)
            ->assertJsonPath('documents.0.title', 'CEBAS')
            ->assertJsonPath('documents.0.status', 'failed')
            ->assertJsonPath('documents.0.error_message', 'Falha ao extrair texto.');
    }

    public function test_reprocess_failed_document_resets_status_and_dispatches_job(): void
    {
        Bus::fake();
        $this->withSession(['documents_admin_authenticated' => true]);

        $document = KnowledgeDocument::create([
            'title' => 'CEBAS',
            'original_name' => 'cebas.txt',
            'stored_path' => 'knowledge/documents/cebas.txt',
            'mime_type' => 'text/plain',
            'extension' => 'txt',
            'size_bytes' => 1000,
            'status' => 'failed',
            'chunks_count' => 0,
            'error_message' => 'Falha ao extrair texto.',
        ]);

        $response = $this->post(route('documents.reprocess', $document));

        $response->assertRedirect(route('documents.index'));
        $this->assertDatabaseHas('knowledge_documents', [
            'id' => $document->id,
            'status' => 'indexing',
            'chunks_count' => 0,
            'error_message' => null,
        ]);
        Bus::assertDispatched(IndexKnowledgeDocument::class, fn (IndexKnowledgeDocument $job) => $job->documentId === $document->id);
    }

    public function test_reprocess_does_not_dispatch_when_document_is_already_indexing(): void
    {
        Bus::fake();
        $this->withSession(['documents_admin_authenticated' => true]);

        $document = KnowledgeDocument::create([
            'title' => 'CEBAS',
            'original_name' => 'cebas.txt',
            'stored_path' => 'knowledge/documents/cebas.txt',
            'mime_type' => 'text/plain',
            'extension' => 'txt',
            'size_bytes' => 1000,
            'status' => 'indexing',
            'chunks_count' => 0,
        ]);

        $response = $this->post(route('documents.reprocess', $document));

        $response->assertRedirect(route('documents.index'));
        Bus::assertNotDispatched(IndexKnowledgeDocument::class);
    }
    public function test_document_store_accepts_manual_text_without_file(): void
    {
        $ingestion = Mockery::mock(KnowledgeIngestionService::class);
        $ingestion->shouldReceive('ingestText')->once()->with('Orientações CEBAS', 'Texto válido para incorporar na base interna.');
        $this->app->instance(KnowledgeIngestionService::class, $ingestion);

        $this->withSession(['documents_admin_authenticated' => true])
            ->from(route('documents.index'))
            ->post(route('documents.text.store'), [
                'manual_title' => 'Orientações CEBAS',
                'manual_text' => 'Texto válido para incorporar na base interna.',
            ])
            ->assertRedirect(route('documents.index'))
            ->assertSessionHas('status', 'Texto recebido. A indexação continuará em segundo plano; acompanhe o status na lista.');
    }

    public function test_manual_text_requires_title(): void
    {
        $ingestion = Mockery::mock(KnowledgeIngestionService::class);
        $ingestion->shouldNotReceive('ingestText');
        $this->app->instance(KnowledgeIngestionService::class, $ingestion);

        $this->withSession(['documents_admin_authenticated' => true])
            ->from(route('documents.index'))
            ->post(route('documents.text.store'), [
                'manual_text' => 'Texto válido para incorporar na base interna.',
            ])
            ->assertRedirect(route('documents.index'))
            ->assertSessionHasErrors('manual_title');
    }

    public function test_manual_text_rejects_blank_or_too_short_text(): void
    {
        $ingestion = Mockery::mock(KnowledgeIngestionService::class);
        $ingestion->shouldNotReceive('ingestText');
        $this->app->instance(KnowledgeIngestionService::class, $ingestion);

        $this->withSession(['documents_admin_authenticated' => true])
            ->from(route('documents.index'))
            ->post(route('documents.text.store'), [
                'manual_title' => 'Texto curto',
                'manual_text' => 'curto',
            ])
            ->assertRedirect(route('documents.index'))
            ->assertSessionHasErrors('manual_text');
    }

    public function test_manual_text_rejects_title_without_sluggable_characters(): void
    {
        $ingestion = Mockery::mock(KnowledgeIngestionService::class);
        $ingestion->shouldReceive('ingestText')->once()->andThrow(new \InvalidArgumentException('Informe um título com letras ou números para identificar o texto.'));
        $this->app->instance(KnowledgeIngestionService::class, $ingestion);

        $this->withSession(['documents_admin_authenticated' => true])
            ->from(route('documents.index'))
            ->post(route('documents.text.store'), [
                'manual_title' => '!!! ###',
                'manual_text' => 'Texto válido para incorporar na base interna.',
            ])
            ->assertRedirect(route('documents.index'))
            ->assertSessionHasErrors('manual_title');
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

    public function test_document_upload_requires_file(): void
    {
        $this->withSession(['documents_admin_authenticated' => true])
            ->from(route('documents.index'))
            ->post(route('documents.store'))
            ->assertRedirect(route('documents.index'))
            ->assertSessionHasErrors('documents');
    }

    public function test_single_document_upload_uses_new_multiple_field(): void
    {
        $file = UploadedFile::fake()->create('certificado.pdf', 120, 'application/pdf');

        $ingestion = Mockery::mock(KnowledgeIngestionService::class);
        $ingestion->shouldReceive('ingest')->once()->with($file);
        $this->app->instance(KnowledgeIngestionService::class, $ingestion);

        $this->withSession(['documents_admin_authenticated' => true])
            ->from(route('documents.index'))
            ->post(route('documents.store'), [
                'documents' => [$file],
            ])
            ->assertRedirect(route('documents.index'))
            ->assertSessionHas('status', 'Documento recebido. A indexação continuará em segundo plano; acompanhe o status na lista.');
    }

    public function test_multiple_document_upload_ingests_each_file(): void
    {
        $pdf = UploadedFile::fake()->create('certificado.pdf', 120, 'application/pdf');
        $docx = UploadedFile::fake()->create('manual.docx', 80, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $ingestion = Mockery::mock(KnowledgeIngestionService::class);
        $ingestion->shouldReceive('ingest')->once()->with($pdf);
        $ingestion->shouldReceive('ingest')->once()->with($docx);
        $this->app->instance(KnowledgeIngestionService::class, $ingestion);

        $this->withSession(['documents_admin_authenticated' => true])
            ->from(route('documents.index'))
            ->post(route('documents.store'), [
                'documents' => [$pdf, $docx],
            ])
            ->assertRedirect(route('documents.index'))
            ->assertSessionHas('status', '2 documentos recebidos. A indexação continuará em segundo plano; acompanhe o status na lista.');
    }

    public function test_document_upload_rejects_invalid_extension(): void
    {
        $ingestion = Mockery::mock(KnowledgeIngestionService::class);
        $ingestion->shouldNotReceive('ingest');
        $this->app->instance(KnowledgeIngestionService::class, $ingestion);

        $this->withSession(['documents_admin_authenticated' => true])
            ->from(route('documents.index'))
            ->post(route('documents.store'), [
                'documents' => [UploadedFile::fake()->create('arquivo.exe', 10, 'application/octet-stream')],
            ])
            ->assertRedirect(route('documents.index'))
            ->assertSessionHasErrors('documents.0');
    }

    public function test_document_upload_rejects_oversized_file(): void
    {
        config(['knowledge.max_upload_mb' => 1]);

        $ingestion = Mockery::mock(KnowledgeIngestionService::class);
        $ingestion->shouldNotReceive('ingest');
        $this->app->instance(KnowledgeIngestionService::class, $ingestion);

        $this->withSession(['documents_admin_authenticated' => true])
            ->from(route('documents.index'))
            ->post(route('documents.store'), [
                'documents' => [UploadedFile::fake()->create('certificado.pdf', 2048, 'application/pdf')],
            ])
            ->assertRedirect(route('documents.index'))
            ->assertSessionHasErrors('documents.0');
    }

    public function test_document_upload_rejects_batch_with_invalid_file(): void
    {
        $ingestion = Mockery::mock(KnowledgeIngestionService::class);
        $ingestion->shouldNotReceive('ingest');
        $this->app->instance(KnowledgeIngestionService::class, $ingestion);

        $this->withSession(['documents_admin_authenticated' => true])
            ->from(route('documents.index'))
            ->post(route('documents.store'), [
                'documents' => [
                    UploadedFile::fake()->create('certificado.pdf', 120, 'application/pdf'),
                    UploadedFile::fake()->create('arquivo.exe', 10, 'application/octet-stream'),
                ],
            ])
            ->assertRedirect(route('documents.index'))
            ->assertSessionHasErrors('documents.1');
    }

    public function test_selected_delete_requires_at_least_one_document(): void
    {
        $ingestion = Mockery::mock(KnowledgeIngestionService::class);
        $ingestion->shouldNotReceive('delete');
        $this->app->instance(KnowledgeIngestionService::class, $ingestion);

        $this->withSession(['documents_admin_authenticated' => true])
            ->from(route('documents.index'))
            ->post(route('documents.destroy-selected'), [
                'documents' => [],
                'password' => 'drsp',
            ])
            ->assertRedirect(route('documents.index'))
            ->assertSessionHasErrors('documents');
    }

    public function test_selected_delete_requires_existing_document_ids(): void
    {
        $ingestion = Mockery::mock(KnowledgeIngestionService::class);
        $ingestion->shouldNotReceive('delete');
        $this->app->instance(KnowledgeIngestionService::class, $ingestion);

        $this->withSession(['documents_admin_authenticated' => true])
            ->from(route('documents.index'))
            ->post(route('documents.destroy-selected'), [
                'documents' => [999999],
                'password' => 'drsp',
            ])
            ->assertRedirect(route('documents.index'))
            ->assertSessionHasErrors('documents.0');
    }

    public function test_selected_delete_requires_password(): void
    {
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
            ->from(route('documents.index'))
            ->post(route('documents.destroy-selected'), [
                'documents' => [$document->id],
            ])
            ->assertRedirect(route('documents.index'))
            ->assertSessionHasErrors('password');
    }
}
