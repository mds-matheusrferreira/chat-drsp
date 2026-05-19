<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    public function index()
    {
        return view('chat');
    }

    public function ask(Request $request)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
        ]);

        $ollamaUrl = rtrim(env('OLLAMA_URL', 'http://127.0.0.1:11434'), '/');
        $model = env('OLLAMA_MODEL', 'gemma3:4b');

        try {
            $response = Http::timeout(120)->post($ollamaUrl.'/api/generate', [
                'model' => $model,
                'prompt' => $data['message'],
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
}
