<?php

namespace Tests\Feature;

use App\Services\Knowledge\KnowledgeSearchService;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
    public function test_chat_fallback_stores_sources_in_session(): void
    {
        Http::fake([
            '127.0.0.1:11434/api/generate' => Http::response([
                'response' => 'CEBAS é a certificação descrita nos documentos internos.',
            ]),
        ]);

        $knowledge = Mockery::mock(KnowledgeSearchService::class);
        $knowledge->shouldReceive('search')->once()->with('O que é CEBAS?')->andReturn([
            [
                'content' => 'A Certificação das Entidades Beneficentes de Assistência Social.',
                'title' => 'CEBAS',
                'original_name' => 'cebas.txt',
                'metadata' => ['extension' => 'txt', 'chunk_index' => 0],
            ],
        ]);
        $knowledge->shouldReceive('contextFromResults')->once()->andReturn('[Trecho 1 — CEBAS]');
        $knowledge->shouldReceive('sourcesFromResults')->once()->andReturn([
            [
                'excerpt_number' => 1,
                'title' => 'CEBAS',
                'original_name' => 'cebas.txt',
                'extension' => 'txt',
                'chunk_index' => 0,
                'excerpt' => 'A Certificação das Entidades Beneficentes de Assistência Social.',
            ],
        ]);

        $this->app->instance(KnowledgeSearchService::class, $knowledge);

        $response = $this->post(route('chat.ask'), ['message' => 'O que é CEBAS?']);

        $response->assertRedirect();
        $response->assertSessionHas('answer', 'CEBAS é a certificação descrita nos documentos internos.');
        $response->assertSessionHas('sources.0.title', 'CEBAS');
        $response->assertSessionHas('sources.0.original_name', 'cebas.txt');
    }
}
