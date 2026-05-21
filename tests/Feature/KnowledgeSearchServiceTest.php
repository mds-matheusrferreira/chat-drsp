<?php

namespace Tests\Feature;

use App\Services\Knowledge\KnowledgePythonProcess;
use App\Services\Knowledge\KnowledgeSearchService;
use Mockery;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class KnowledgeSearchServiceTest extends TestCase
{
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
        $process = Mockery::mock(Process::class);
        $process->shouldReceive('failed')->once()->andReturnFalse();
        $process->shouldReceive('output')->once()->andReturn("Loading weights...\n".json_encode([
            'results' => [
                [
                    'content' => 'A Certificação das Entidades Beneficentes de Assistência Social.',
                    'title' => 'CEBAS',
                    'original_name' => 'cebas.txt',
                ],
            ],
        ], JSON_UNESCAPED_UNICODE));

        $python = Mockery::mock(KnowledgePythonProcess::class);
        $python->shouldReceive('run')->once()->andReturn($process);

        $results = (new KnowledgeSearchService($python))->search('O que é cebas?');

        $this->assertSame('CEBAS', $results[0]['title']);
    }
}
