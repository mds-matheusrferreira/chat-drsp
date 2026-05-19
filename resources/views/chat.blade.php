<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat DRSP</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f7f8fb;
            --surface: #ffffff;
            --surface-soft: #f1f5f9;
            --border: #e2e8f0;
            --text: #0f172a;
            --muted: #64748b;
            --primary: #1d4ed8;
            --primary-dark: #1e40af;
            --primary-soft: #eff6ff;
            --danger: #b91c1c;
            --danger-soft: #fef2f2;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background: var(--bg);
            color: var(--text);
        }

        button,
        textarea {
            font: inherit;
        }

        .page {
            width: min(100%, 1040px);
            margin: 0 auto;
            padding: 32px 16px;
        }

        .topbar {
            display: flex;
            gap: 16px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }

        .brand {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .mark {
            display: grid;
            width: 42px;
            height: 42px;
            place-items: center;
            border-radius: 14px;
            background: var(--text);
            color: #ffffff;
            font-weight: 800;
        }

        h1,
        h2,
        p {
            margin-top: 0;
        }

        h1 {
            margin-bottom: 2px;
            font-size: 22px;
            letter-spacing: -0.03em;
        }

        .subtitle,
        .muted,
        .note {
            color: var(--muted);
            line-height: 1.6;
        }

        .subtitle {
            margin-bottom: 0;
            font-size: 14px;
        }

        .status {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            padding: 9px 12px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: var(--surface);
            color: #334155;
            font-size: 13px;
            font-weight: 700;
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #22c55e;
        }

        .layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 280px;
            gap: 18px;
        }

        .card,
        .panel {
            border: 1px solid var(--border);
            border-radius: 24px;
            background: var(--surface);
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
        }

        .card {
            padding: clamp(18px, 4vw, 28px);
        }

        .card-header {
            display: flex;
            gap: 14px;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 22px;
        }

        h2 {
            margin-bottom: 6px;
            font-size: clamp(24px, 4vw, 34px);
            letter-spacing: -0.05em;
        }

        .limit {
            flex-shrink: 0;
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }

        form {
            display: grid;
            gap: 16px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 800;
        }

        textarea {
            width: 100%;
            min-height: 220px;
            padding: 16px;
            resize: vertical;
            border: 1px solid #cbd5e1;
            border-radius: 18px;
            outline: none;
            background: #ffffff;
            color: var(--text);
            font-size: 16px;
            line-height: 1.7;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(29, 78, 216, 0.10);
        }

        .quick {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .chip {
            border: 1px solid var(--border);
            border-radius: 999px;
            background: var(--surface-soft);
            color: #334155;
            cursor: pointer;
            padding: 8px 11px;
            font-size: 14px;
            font-weight: 700;
        }

        .chip:hover {
            border-color: #bfdbfe;
            color: var(--primary);
        }

        .actions {
            display: flex;
            gap: 14px;
            align-items: center;
            justify-content: space-between;
            padding-top: 2px;
        }

        .submit {
            border: 0;
            border-radius: 14px;
            background: var(--primary);
            color: #ffffff;
            cursor: pointer;
            padding: 13px 18px;
            font-weight: 800;
        }

        .submit:hover {
            background: var(--primary-dark);
        }

        .alert {
            margin-top: 20px;
            border-radius: 18px;
            padding: 18px;
            line-height: 1.75;
            white-space: pre-wrap;
        }

        .answer {
            border: 1px solid #bfdbfe;
            background: var(--primary-soft);
            color: #1e293b;
        }

        .answer-title {
            margin-bottom: 10px;
            color: var(--primary);
            font-size: 13px;
            font-weight: 900;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .error {
            border: 1px solid #fecaca;
            background: var(--danger-soft);
            color: var(--danger);
            font-weight: 700;
        }

        .side {
            display: grid;
            gap: 14px;
            align-content: start;
        }

        .panel {
            padding: 18px;
        }

        .panel h2 {
            margin-bottom: 10px;
            font-size: 17px;
            letter-spacing: -0.02em;
        }

        .panel ul {
            display: grid;
            gap: 10px;
            margin: 0;
            padding-left: 18px;
            color: var(--muted);
            line-height: 1.55;
        }

        .panel p {
            margin-bottom: 0;
            color: var(--muted);
            line-height: 1.6;
        }

        @media (max-width: 820px) {
            .topbar,
            .card-header,
            .actions {
                flex-direction: column;
                align-items: stretch;
            }

            .layout {
                grid-template-columns: 1fr;
            }

            .status,
            .submit {
                width: 100%;
                justify-content: center;
                text-align: center;
            }
        }

        @media (max-width: 520px) {
            .page {
                padding: 18px 12px;
            }

            .brand {
                align-items: flex-start;
            }

            .mark {
                width: 38px;
                height: 38px;
                border-radius: 12px;
            }

            textarea {
                min-height: 190px;
            }

            .quick {
                display: grid;
            }
        }
    </style>
</head>
<body>
    <main class="page">
        <header class="topbar">
            <div class="brand">
                <div class="mark">DR</div>
                <div>
                    <h1>Chat DRSP</h1>
                    <p class="subtitle">Assistente local para consultas rápidas.</p>
                </div>
            </div>

            <div class="status">
                <span class="dot" aria-hidden="true"></span>
                Ollama local
            </div>
        </header>

        <section class="layout">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>Faça sua pergunta</h2>
                        <p class="muted">Escreva com contexto e escolha o formato da resposta.</p>
                    </div>
                    <span class="limit">máx. 4000 caracteres</span>
                </div>

                <form method="POST" action="{{ route('chat.ask') }}">
                    @csrf
                    <div>
                        <label for="message">Mensagem</label>
                        <textarea id="message" name="message" placeholder="Ex.: Resuma este texto em tópicos objetivos..." required>{{ old('message') }}</textarea>
                        @error('message')
                            <div class="alert error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="quick" aria-label="Exemplos rápidos">
                        <button type="button" class="chip" data-prompt="Resuma este texto em tópicos objetivos:" onclick="document.getElementById('message').value = this.dataset.prompt; document.getElementById('message').focus();">Resumir</button>
                        <button type="button" class="chip" data-prompt="Explique este assunto de forma simples e prática:" onclick="document.getElementById('message').value = this.dataset.prompt; document.getElementById('message').focus();">Explicar</button>
                        <button type="button" class="chip" data-prompt="Liste riscos, pontos de atenção e próximos passos sobre:" onclick="document.getElementById('message').value = this.dataset.prompt; document.getElementById('message').focus();">Analisar</button>
                    </div>

                    <div class="actions">
                        <p class="note">A resposta pode levar alguns segundos.</p>
                        <button type="submit" class="submit">Enviar</button>
                    </div>
                </form>

                @if (session('answer'))
                    <article class="alert answer">
                        <div class="answer-title">Resposta</div>
                        <div>{{ session('answer') }}</div>
                    </article>
                @endif

                @if (session('error'))
                    <div class="alert error">{{ session('error') }}</div>
                @endif
            </div>

            <aside class="side">
                <div class="panel">
                    <h2>Dicas</h2>
                    <ul>
                        <li>Informe contexto e objetivo.</li>
                        <li>Peça tópicos, passos ou tabela.</li>
                        <li>Revise respostas críticas.</li>
                    </ul>
                </div>

                <div class="panel">
                    <h2>Antes de usar</h2>
                    <p>Confirme se o Ollama e o modelo configurado estão rodando.</p>
                </div>
            </aside>
        </section>
    </main>
</body>
</html>
