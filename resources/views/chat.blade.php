<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat DRSP</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #eef3fb;
            --glass: rgba(255, 255, 255, 0.72);
            --glass-strong: rgba(255, 255, 255, 0.88);
            --surface: rgba(255, 255, 255, 0.78);
            --surface-soft: rgba(248, 250, 252, 0.76);
            --border: rgba(148, 163, 184, 0.24);
            --border-strong: rgba(148, 163, 184, 0.36);
            --text: #111827;
            --muted: #667085;
            --primary: #0a84ff;
            --primary-dark: #0068d9;
            --primary-soft: rgba(10, 132, 255, 0.10);
            --danger: #b42318;
            --danger-soft: rgba(254, 228, 226, 0.78);
            --shadow: 0 30px 80px rgba(15, 23, 42, 0.14), 0 12px 32px rgba(15, 23, 42, 0.08);
            --shadow-soft: 0 16px 40px rgba(15, 23, 42, 0.08);
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", Inter, ui-sans-serif, system-ui, sans-serif;
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
            background:
                radial-gradient(circle at 12% 8%, rgba(10, 132, 255, 0.22), transparent 30%),
                radial-gradient(circle at 88% 14%, rgba(175, 82, 222, 0.18), transparent 28%),
                radial-gradient(circle at 50% 92%, rgba(90, 200, 250, 0.18), transparent 34%),
                linear-gradient(135deg, #f7faff 0%, #eef3fb 48%, #f8fbff 100%);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
            text-rendering: geometricPrecision;
        }

        body::before {
            position: fixed;
            inset: 0;
            z-index: -1;
            background-image: linear-gradient(rgba(255, 255, 255, 0.22) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, 0.18) 1px, transparent 1px);
            background-size: 42px 42px;
            content: "";
            mask-image: radial-gradient(circle at center, black, transparent 72%);
        }

        button,
        textarea {
            font: inherit;
        }

        button {
            -webkit-tap-highlight-color: transparent;
        }

        .page {
            width: min(100%, 1120px);
            margin: 0 auto;
            padding: 38px 18px;
        }

        .topbar {
            display: flex;
            gap: 16px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
            padding: 0 6px;
        }

        .brand {
            display: flex;
            gap: 13px;
            align-items: center;
        }

        .mark {
            display: grid;
            width: 46px;
            height: 46px;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, 0.72);
            border-radius: 16px;
            background: linear-gradient(145deg, #1d1d1f 0%, #334155 54%, #0a84ff 140%);
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.20), inset 0 1px 0 rgba(255, 255, 255, 0.24);
            color: #ffffff;
            font-size: 14px;
            font-weight: 900;
            letter-spacing: -0.04em;
        }

        h1,
        h2,
        p {
            margin-top: 0;
        }

        h1 {
            margin-bottom: 2px;
            font-size: 23px;
            letter-spacing: -0.045em;
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
            padding: 10px 13px;
            border: 1px solid rgba(255, 255, 255, 0.68);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.66);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.80), 0 10px 24px rgba(15, 23, 42, 0.08);
            color: #344054;
            font-size: 13px;
            font-weight: 800;
            backdrop-filter: blur(18px) saturate(150%);
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #32d74b;
            box-shadow: 0 0 0 4px rgba(50, 215, 75, 0.13), 0 0 18px rgba(50, 215, 75, 0.55);
        }

        .layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 292px;
            gap: 18px;
            align-items: start;
        }

        .card,
        .panel {
            border: 1px solid var(--border);
            background: var(--glass);
            box-shadow: var(--shadow-soft);
            backdrop-filter: blur(26px) saturate(170%);
            -webkit-backdrop-filter: blur(26px) saturate(170%);
        }

        .card {
            overflow: hidden;
            border-radius: 30px;
            box-shadow: var(--shadow);
        }

        .window-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 48px;
            padding: 14px 18px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.18);
            background: rgba(255, 255, 255, 0.44);
        }

        .window-title {
            color: #667085;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: -0.01em;
        }

        .window-spacer {
            width: 56px;
        }

        .card-body {
            padding: clamp(20px, 4vw, 32px);
        }

        .card-header {
            display: flex;
            gap: 14px;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        h2 {
            margin-bottom: 7px;
            font-size: clamp(26px, 4vw, 38px);
            letter-spacing: -0.06em;
            line-height: 1.02;
        }

        .limit {
            flex-shrink: 0;
            padding: 7px 10px;
            border: 1px solid rgba(148, 163, 184, 0.22);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.54);
            color: var(--muted);
            font-size: 12px;
            font-weight: 800;
        }

        form {
            display: grid;
            gap: 16px;
        }

        label {
            display: block;
            margin-bottom: 9px;
            color: #1f2937;
            font-size: 14px;
            font-weight: 850;
            letter-spacing: -0.01em;
        }

        textarea {
            width: 100%;
            min-height: 230px;
            padding: 17px 18px;
            resize: vertical;
            border: 1px solid var(--border-strong);
            border-radius: 20px;
            outline: none;
            background: rgba(255, 255, 255, 0.82);
            box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.04), 0 1px 0 rgba(255, 255, 255, 0.7);
            color: var(--text);
            font-size: 16px;
            line-height: 1.7;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        textarea::placeholder {
            color: #98a2b3;
        }

        textarea:focus {
            border-color: rgba(10, 132, 255, 0.72);
            background: rgba(255, 255, 255, 0.94);
            box-shadow: 0 0 0 4px rgba(10, 132, 255, 0.14), inset 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .quick {
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
        }

        .chip {
            border: 1px solid rgba(148, 163, 184, 0.26);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.58);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.72);
            color: #344054;
            cursor: pointer;
            padding: 9px 13px;
            font-size: 14px;
            font-weight: 800;
            transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease, color 0.18s ease, background 0.18s ease;
        }

        .chip:hover {
            transform: translateY(-1px);
            border-color: rgba(10, 132, 255, 0.32);
            background: rgba(10, 132, 255, 0.08);
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
            color: var(--primary-dark);
        }

        .chip:active {
            transform: translateY(0);
        }

        .actions {
            display: flex;
            gap: 14px;
            align-items: center;
            justify-content: space-between;
            padding-top: 4px;
        }

        .note {
            margin-bottom: 0;
            font-size: 14px;
        }

        .submit {
            border: 0;
            border-radius: 15px;
            background: linear-gradient(180deg, #37a1ff 0%, #0a84ff 52%, #0068d9 100%);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.34), 0 14px 28px rgba(10, 132, 255, 0.26);
            color: #ffffff;
            cursor: pointer;
            padding: 13px 20px;
            font-weight: 900;
            letter-spacing: -0.01em;
            transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
        }

        .submit:hover {
            transform: translateY(-1px);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.38), 0 18px 34px rgba(10, 132, 255, 0.30);
            filter: saturate(1.04);
        }

        .submit:active {
            transform: translateY(1px);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.26), 0 9px 20px rgba(10, 132, 255, 0.22);
        }

        .alert {
            margin-top: 22px;
            border-radius: 22px;
            padding: 18px;
            line-height: 1.75;
            white-space: pre-wrap;
        }

        .answer {
            border: 1px solid rgba(10, 132, 255, 0.22);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.86), rgba(10, 132, 255, 0.08));
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.76);
            color: #1e293b;
        }

        .answer-title {
            display: inline-flex;
            align-items: center;
            margin-bottom: 11px;
            color: var(--primary-dark);
            font-size: 12px;
            font-weight: 950;
            letter-spacing: 0.13em;
            text-transform: uppercase;
        }

        .answer-title::before {
            width: 8px;
            height: 8px;
            margin-right: 8px;
            border-radius: 999px;
            background: var(--primary);
            box-shadow: 0 0 0 4px rgba(10, 132, 255, 0.12);
            content: "";
        }

        .error {
            border: 1px solid rgba(240, 68, 56, 0.24);
            background: var(--danger-soft);
            color: var(--danger);
            font-weight: 750;
        }

        .side {
            display: grid;
            gap: 14px;
            align-content: start;
        }

        .panel {
            border-radius: 24px;
            padding: 19px;
        }

        .panel h2 {
            margin-bottom: 12px;
            font-size: 17px;
            letter-spacing: -0.03em;
            line-height: 1.2;
        }

        .panel ul {
            display: grid;
            gap: 11px;
            margin: 0;
            padding-left: 18px;
            color: var(--muted);
            line-height: 1.55;
        }

        .panel li::marker {
            color: var(--primary);
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

            .limit {
                width: fit-content;
            }
        }

        @media (max-width: 520px) {
            .page {
                padding: 20px 12px;
            }

            .topbar {
                padding: 0;
            }

            .brand {
                align-items: flex-start;
            }

            .mark {
                width: 40px;
                height: 40px;
                border-radius: 14px;
            }

            .window-title,
            .window-spacer {
                display: none;
            }

            .card {
                border-radius: 24px;
            }

            .card-body {
                padding: 20px;
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
                <div class="window-bar" aria-hidden="true">
                    <div class="window-title">chat-drsp.local</div>
                    <div class="window-spacer"></div>
                </div>

                <div class="card-body">
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
                            <button type="button" class="chip" data-prompt="Resuma este texto em tópicos objetivos:">Resumir</button>
                            <button type="button" class="chip" data-prompt="Explique este assunto de forma simples e prática:">Explicar</button>
                            <button type="button" class="chip" data-prompt="Liste riscos, pontos de atenção e próximos passos sobre:">Analisar</button>
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

    <script>
        document.querySelectorAll('[data-prompt]').forEach((button) => {
            button.addEventListener('click', () => {
                const textarea = document.getElementById('message');

                textarea.value = button.dataset.prompt;
                textarea.focus();
            });
        });
    </script>
</body>
</html>
