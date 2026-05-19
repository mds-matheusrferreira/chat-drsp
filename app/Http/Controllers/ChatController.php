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

    public function ask(Request $request, KnowledgeSearchService $knowledge)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
        ]);

        $ollamaUrl = $this->ollamaUrl();
        $model = $this->ollamaModel();
        $prompt = $this->prompt($data['message'], $knowledge->contextFor($data['message']));

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
                ->with('answer', $response->json('response', 'Sem resposta retornada pelo modelo.'));
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Não foi possível conectar ao Ollama em '.$ollamaUrl.'. Inicie o Ollama e tente novamente.');
        }
    }

    public function stream(Request $request, KnowledgeSearchService $knowledge)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
        ]);

        $prompt = $this->prompt($data['message'], $knowledge->contextFor($data['message']));

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

    private function prompt(string $message, string $knowledgeContext = ''): string
    {
        $prompt = <<<'PROMPT'
Você é um assistente interno do DRSP — Departamento de Rede Socioassistencial Privada do SUAS.
Responda sempre em português do Brasil.
Use linguagem clara, objetiva e institucional.
Use os documentos internos fornecidos como principal referência quando eles forem relevantes.
Quando a informação não estiver nos documentos fornecidos, diga que não encontrou informação suficiente na base interna.
Não invente normas, prazos, números ou procedimentos.
Para temas sensíveis, recomende validação com a equipe responsável.
PROMPT;

        if ($knowledgeContext !== '') {
            $prompt .= "\n\nContexto de documentos internos:\n".$knowledgeContext;
        }

        return $prompt."\n\nPergunta do usuário:\n".$message;
    }
}
