<?php

namespace App\Http\Controllers;

use App\Services\Knowledge\KnowledgeSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    public function index()
    {
        return view('chat');
    }

    public function health()
    {
        $ollamaUrl = $this->ollamaUrl();
        $model = $this->ollamaModel();

        try {
            $response = Http::timeout(3)->get($ollamaUrl.'/api/tags');

            if (! $response->successful()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Ollama respondeu HTTP '.$response->status(),
                ], 503);
            }

            $models = collect($response->json('models', []));
            $modelAvailable = $models->contains(fn (array $item) => ($item['name'] ?? $item['model'] ?? null) === $model);

            if (! $modelAvailable) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Modelo '.$model.' não encontrado no Ollama',
                ], 503);
            }

            return response()->json([
                'ok' => true,
                'message' => 'Ollama conectado'
            ]);
        } catch (\Throwable) {
            return response()->json([
                'ok' => false,
                'message' => 'Ollama indisponível'
            ], 503);
        }
    }

    public function ask(Request $request, KnowledgeSearchService $knowledge)
    {
        $data = $this->validatedChatData($request);

        $ollamaUrl = $this->ollamaUrl();
        $model = $this->ollamaModel();
        $history = $this->normalizedHistory($data['history'] ?? []);
        $searchQuery = $this->searchQuery($data['message'], $history);
        $results = $knowledge->search($searchQuery);
        $prompt = $this->prompt($data['message'], $knowledge->contextFromResults($results), $history);
        $sources = $knowledge->sourcesFromResults($results);

        try {
            $response = Http::timeout(120)->post($ollamaUrl.'/api/generate', [
                'model' => $model,
                'prompt' => $prompt,
                'stream' => false,
            ]);

            if (! $response->successful()) {
                return back()
                    ->withInput()
                    ->with('error', 'Ollama respondeu com erro HTTP '.$response->status().'. Verifique se o serviço e o modelo estão disponíveis.');
            }

            return back()
                ->withInput()
                ->with('answer', $response->json('response', 'Sem resposta retornada pelo modelo.'))
                ->with('sources', $sources);
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Não foi possível conectar ao Ollama em '.$ollamaUrl.'. Inicie o Ollama e tente novamente.');
        }
    }

    public function stream(Request $request, KnowledgeSearchService $knowledge)
    {
        $data = $this->validatedChatData($request);

        $history = $this->normalizedHistory($data['history'] ?? []);
        $searchQuery = $this->searchQuery($data['message'], $history);
        $results = $knowledge->search($searchQuery);
        $prompt = $this->prompt($data['message'], $knowledge->contextFromResults($results), $history);
        $sources = $knowledge->sourcesFromResults($results);

        return response()->stream(function () use ($prompt) {
            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            $finished = false;
            $handle = curl_init($this->ollamaUrl().'/api/generate');

            curl_setopt_array($handle, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS => json_encode([
                    'model' => $this->ollamaModel(),
                    'prompt' => $prompt,
                    'stream' => true,
                ], JSON_THROW_ON_ERROR),
                CURLOPT_TIMEOUT => 0,
                CURLOPT_WRITEFUNCTION => function ($curl, string $chunk) use (&$finished) {
                    static $buffer = '';

                    $buffer .= $chunk;

                    while (($position = strpos($buffer, "\n")) !== false) {
                        $line = trim(substr($buffer, 0, $position));
                        $buffer = substr($buffer, $position + 1);

                        if ($line === '') {
                            continue;
                        }

                        $payload = json_decode($line, true);

                        if (! is_array($payload)) {
                            continue;
                        }

                        if (isset($payload['response'])) {
                            echo $payload['response'];

                            if (ob_get_level() > 0) {
                                ob_flush();
                            }

                            flush();
                        }

                        if (($payload['done'] ?? false) === true) {
                            $finished = true;
                        }
                    }

                    return strlen($chunk);
                },
            ]);

            $success = curl_exec($handle);

            if ($success === false && ! $finished) {
                echo "\nNão foi possível conectar ao Ollama em ".$this->ollamaUrl().'. Inicie o Ollama e tente novamente.';
                flush();
            }

            curl_close($handle);
        }, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-transform',
            'X-Accel-Buffering' => 'no',
            'X-Knowledge-Sources' => base64_encode(json_encode($sources, JSON_UNESCAPED_UNICODE)),
        ]);
    }

    private function ollamaUrl(): string
    {
        return rtrim(env('OLLAMA_URL', 'http://127.0.0.1:11434'), '/');
    }

    private function ollamaModel(): string
    {
        return env('OLLAMA_MODEL', 'gemma3:4b');
    }

    private function validatedChatData(Request $request): array
    {
        return $request->validate([
            'message' => ['required', 'string', 'max:4000'],
            'history' => ['sometimes', 'array', 'max:12'],
            'history.*.role' => ['required_with:history', 'string', 'in:user,assistant'],
            'history.*.content' => ['required_with:history', 'string', 'max:2000'],
        ]);
    }

    private function normalizedHistory(array $history): array
    {
        return collect($history)
            ->filter(fn (array $item) => in_array($item['role'] ?? null, ['user', 'assistant'], true) && trim($item['content'] ?? '') !== '')
            ->slice(-8)
            ->map(fn (array $item) => [
                'role' => $item['role'],
                'content' => mb_substr(trim($item['content']), 0, 1600),
            ])
            ->values()
            ->all();
    }

    private function searchQuery(string $message, array $history): string
    {
        $recentUserMessages = collect($history)
            ->where('role', 'user')
            ->pluck('content')
            ->slice(-3)
            ->implode("\n");

        return trim($recentUserMessages."\n".$message);
    }

    private function conversationContext(array $history): string
    {
        if ($history === []) {
            return '';
        }

        return collect($history)
            ->map(fn (array $item) => ($item['role'] === 'user' ? 'Usuário: ' : 'Assistente: ').$item['content'])
            ->implode("\n");
    }

    private function prompt(string $message, string $knowledgeContext = '', array $history = []): string
    {
        $prompt = <<<'PROMPT'
Você é um assistente interno do DRSP — Departamento de Rede Socioassistencial Privada do SUAS.
Responda sempre em português do Brasil.
Use linguagem clara, objetiva e institucional.
Responda somente com base no contexto de documentos internos fornecido quando houver contexto.
Quando a informação não estiver nos documentos fornecidos, diga que não encontrou informação suficiente na base interna.
Não invente normas, prazos, números, procedimentos ou conceitos que não apareçam no contexto.
Para temas sensíveis, recomende validação com a equipe responsável.
Não liste fontes no texto da resposta; a aplicação exibirá as fontes recuperadas em bloco separado.

Exemplos de comportamento esperado:
Usuário: O que é CEBAS?
Resposta: CEBAS é a Certificação de Entidades Beneficentes de Assistência Social, conforme descrito nos documentos internos recuperados.

Usuário: O que é CNEAS?
Resposta: CNEAS é tratado nos documentos internos como cadastro relacionado às organizações e ofertas socioassistenciais. Responda apenas com os detalhes presentes no contexto recuperado.

Usuário: O que é a Rede Socioassistencial Privada?
Resposta: A Rede Socioassistencial Privada deve ser explicada conforme os documentos internos recuperados, em linguagem institucional e objetiva.

Usuário: Qual é o prazo de um procedimento que não aparece no contexto?
Resposta: Não encontrei informação suficiente na base interna para responder com segurança. Valide com a equipe responsável.
PROMPT;

        $conversationContext = $this->conversationContext($history);

        if ($conversationContext !== '') {
            $prompt .= "\n\nHistórico recente da conversa:\n".$conversationContext;
            $prompt .= "\n\nUse o histórico apenas para resolver referências como 'isso', 'ele', 'quais documentos' ou 'sobre o tema anterior'.";
        }

        if ($knowledgeContext !== '') {
            $prompt .= "\n\nContexto de documentos internos:\n".$knowledgeContext;
        }

        return $prompt."\n\nPergunta do usuário:\n".$message;
    }
}
