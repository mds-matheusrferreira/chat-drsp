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
            '*/api/generate' => Http::response([
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

        $response = $this->post('/chat', ['message' => 'O que é CEBAS?']);

        $response->assertRedirect();
        $response->assertSessionHas('answer', 'CEBAS é a certificação descrita nos documentos internos.');
        $response->assertSessionHas('sources.0.title', 'CEBAS');
        $response->assertSessionHas('sources.0.original_name', 'cebas.txt');
    }

    public function test_chat_search_rewrites_follow_up_questions_with_conversation_subjects(): void
    {
        $controller = new \App\Http\Controllers\ChatController;
        $method = new \ReflectionMethod($controller, 'searchQuery');
        $method->setAccessible(true);

        $query = $method->invoke($controller, 'quais os documentos necessários?', [
            [
                'role' => 'assistant',
                'content' => 'CEBAS é a Certificação de Entidades Beneficentes de Assistência Social.',
            ],
        ]);

        $this->assertSame(
            'quais os documentos necessários? requerimento documentos obrigatórios acompanhado dos seguintes documentos CEBAS certificação assistência social',
            $query
        );
    }
}
