<?php

namespace Tests\Feature;

use App\Models\KnowledgeDocument;
use App\Services\Knowledge\KnowledgePythonProcess;
use App\Services\Knowledge\KnowledgeSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class KnowledgeSearchServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_python_process_prepares_project_temp_directory(): void
    {
        config(['knowledge.tmp_path' => storage_path('framework/testing/knowledge-search-tmp')]);

        $expectedTempPath = storage_path('framework/testing/knowledge-search-tmp');
        $environment = app(KnowledgePythonProcess::class)->environment();

        $this->assertDirectoryExists($expectedTempPath);
        $this->assertSame($expectedTempPath, $environment['TMP']);
        $this->assertSame($expectedTempPath, $environment['TEMP']);
        $this->assertSame($expectedTempPath, $environment['TMPDIR']);
        $this->assertSame($expectedTempPath, getenv('TMP'));
        $this->assertSame($expectedTempPath, getenv('TEMP'));
        $this->assertSame($expectedTempPath, getenv('TMPDIR'));
        $this->assertSame(config('knowledge.chroma_path'), $environment['KNOWLEDGE_CHROMA_PATH']);
    }

    public function test_search_decodes_json_when_python_writes_extra_output(): void
    {
        $document = KnowledgeDocument::create([
            'title' => 'CEBAS',
            'original_name' => 'cebas.txt',
            'stored_path' => 'knowledge/documents/cebas.txt',
            'mime_type' => 'text/plain',
            'extension' => 'txt',
            'size_bytes' => 1000,
            'status' => 'ready',
        ]);

        $process = Mockery::mock(Process::class);
        $process->shouldReceive('failed')->once()->andReturnFalse();
        $process->shouldReceive('output')->once()->andReturn("Loading weights...\n".json_encode([
            'results' => [
                [
                    'content' => 'A Certificação das Entidades Beneficentes de Assistência Social.',
                    'title' => 'CEBAS',
                    'original_name' => 'cebas.txt',
                    'metadata' => ['document_id' => (string) $document->id],
                ],
            ],
        ], JSON_UNESCAPED_UNICODE));

        $python = Mockery::mock(KnowledgePythonProcess::class);
        $python->shouldReceive('run')->once()->andReturn($process);

        $results = (new KnowledgeSearchService($python))->search('O que é cebas?');

        $this->assertSame('CEBAS', $results[0]['title']);
    }

    public function test_search_discards_results_without_document_id(): void
    {
        $process = Mockery::mock(Process::class);
        $process->shouldReceive('failed')->once()->andReturnFalse();
        $process->shouldReceive('output')->once()->andReturn(json_encode([
            'results' => [
                [
                    'content' => 'Chunk legado sem vínculo com documento.',
                    'title' => 'Legado',
                    'original_name' => 'legado.txt',
                    'metadata' => ['extension' => 'txt'],
                ],
            ],
        ], JSON_UNESCAPED_UNICODE));

        $python = Mockery::mock(KnowledgePythonProcess::class);
        $python->shouldReceive('run')->once()->andReturn($process);

        $results = (new KnowledgeSearchService($python))->search('legado');

        $this->assertSame([], $results);
    }

    public function test_context_and_sources_are_formatted_from_same_results(): void
    {
        $service = new KnowledgeSearchService(Mockery::mock(KnowledgePythonProcess::class));
        $results = [
            [
                'content' => "A Certificação\n das Entidades Beneficentes de Assistência Social é concedida às OSCs.",
                'title' => 'CEBAS',
                'original_name' => 'cebas.txt',
                'metadata' => [
                    'extension' => 'txt',
                    'chunk_index' => 0,
                ],
            ],
        ];

        $context = $service->contextFromResults($results);
        $sources = $service->sourcesFromResults($results);

        $this->assertStringContainsString('[Trecho 1 — CEBAS]', $context);
        $this->assertSame('CEBAS', $sources[0]['title']);
        $this->assertSame('cebas.txt', $sources[0]['original_name']);
        $this->assertSame('txt', $sources[0]['extension']);
        $this->assertSame(0, $sources[0]['chunk_index']);
        $this->assertStringContainsString('Certificação das Entidades', $sources[0]['excerpt']);
    }
}
