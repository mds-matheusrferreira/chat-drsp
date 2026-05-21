<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos DRSP</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #eef3fb;
            --glass: rgba(255, 255, 255, 0.78);
            --border: rgba(148, 163, 184, 0.26);
            --text: #111827;
            --muted: #667085;
            --primary: #0a84ff;
            --danger: #b42318;
            --success: #067647;
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", Inter, ui-sans-serif, system-ui, sans-serif;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            margin: 0;
            background:
                radial-gradient(circle at 12% 8%, rgba(10, 132, 255, 0.22), transparent 30%),
                radial-gradient(circle at 88% 14%, rgba(175, 82, 222, 0.16), transparent 28%),
                linear-gradient(135deg, #f7faff 0%, #eef3fb 48%, #f8fbff 100%);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
        }

        .page {
            width: min(100%, 1040px);
            margin: 0 auto;
            padding: 38px 18px;
        }

        .topbar {
            display: flex;
            gap: 16px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
        }

        h1, h2, p { margin-top: 0; }

        h1 {
            margin-bottom: 4px;
            font-size: 28px;
            letter-spacing: -0.05em;
        }

        .muted {
            color: var(--muted);
            line-height: 1.6;
        }

        .link, button {
            border: 0;
            border-radius: 14px;
            background: linear-gradient(180deg, #37a1ff 0%, #0a84ff 55%, #0068d9 100%);
            color: #fff;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 16px;
            text-decoration: none;
            font-weight: 850;
        }

        .card {
            border: 1px solid var(--border);
            border-radius: 28px;
            background: var(--glass);
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.12);
            backdrop-filter: blur(24px) saturate(160%);
            padding: 24px;
        }

        .grid {
            display: grid;
            grid-template-columns: 360px 1fr;
            gap: 18px;
            align-items: start;
        }

        form {
            display: grid;
            gap: 14px;
        }

        label {
            color: #1f2937;
            font-weight: 800;
        }

        input[type="text"], input[type="file"], input[type="password"], textarea {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.82);
            color: var(--text);
            padding: 13px 14px;
            font: inherit;
        }

        textarea {
            min-height: 220px;
            resize: vertical;
        }

        .alert {
            margin-bottom: 16px;
            border-radius: 16px;
            padding: 13px 14px;
            background: rgba(236, 253, 243, 0.86);
            color: var(--success);
            font-weight: 750;
        }

        .error {
            background: rgba(254, 228, 226, 0.86);
            color: var(--danger);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            border-radius: 18px;
        }

        th, td {
            border-bottom: 1px solid rgba(148, 163, 184, 0.18);
            padding: 12px 10px;
            text-align: left;
            vertical-align: top;
        }

        th {
            color: var(--muted);
            font-size: 12px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .badge {
            display: inline-flex;
            border-radius: 999px;
            background: rgba(10, 132, 255, 0.10);
            color: #0068d9;
            padding: 5px 9px;
            font-size: 12px;
            font-weight: 850;
        }

        .badge.ready { background: rgba(236, 253, 243, 0.9); color: var(--success); }
        .badge.failed { background: rgba(254, 228, 226, 0.9); color: var(--danger); }

        .delete {
            background: rgba(254, 228, 226, 0.9);
            color: var(--danger);
            padding: 8px 10px;
        }

        .danger-panel {
            margin-bottom: 16px;
            border: 1px solid rgba(180, 35, 24, 0.16);
            border-radius: 18px;
            background: rgba(254, 228, 226, 0.35);
            padding: 14px;
        }

        .danger-panel form {
            grid-template-columns: 1fr auto;
            align-items: end;
        }

        .select-cell {
            width: 42px;
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--danger);
        }

        @media (max-width: 820px) {
            .topbar, .grid { grid-template-columns: 1fr; flex-direction: column; align-items: stretch; }
            .link { width: 100%; }
            table { display: block; overflow-x: auto; }
        }
    </style>
</head>
<body>
    <main class="page">
        <header class="topbar">
            <div>
                <h1>Documentos internos</h1>
                <p class="muted">Envie arquivos para alimentar a base de conhecimento do DRSP/SUAS.</p>
            </div>
            <a class="link" href="{{ route('chat.index') }}">Voltar ao chat</a>
        </header>

        @if (session('status'))
            <div class="alert">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert error">{{ $errors->first() }}</div>
        @endif

        <section class="grid">
            <div class="card">
                <h2>Novos documentos</h2>
                <p class="muted">Formatos aceitos: {{ implode(', ', $allowedExtensions) }}. Selecione um ou mais arquivos; cada documento usará o próprio nome do arquivo como título.</p>

                <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div>
                        <label for="documents">Arquivos</label>
                        <input id="documents" type="file" name="documents[]" accept="{{ collect($allowedExtensions)->map(fn ($extension) => '.'.$extension)->implode(',') }}" multiple required>
                    </div>
                    <button type="submit">Enviar e indexar documentos</button>
                </form>

                <hr style="width: 100%; border: 0; border-top: 1px solid rgba(148, 163, 184, 0.22); margin: 24px 0;">

                <h2>Inserir texto na base</h2>
                <p class="muted">Cole um conteúdo textual para incorporar diretamente à base. Se repetir o mesmo título, o texto anterior será substituído.</p>

                <form method="POST" action="{{ route('documents.text.store') }}">
                    @csrf
                    <div>
                        <label for="manual_title">Nome/título da base</label>
                        <input id="manual_title" type="text" name="manual_title" placeholder="Ex.: Fluxo de atendimento SUAS" value="{{ old('manual_title') }}" required>
                    </div>
                    <div>
                        <label for="manual_text">Insira texto para incorporar base</label>
                        <textarea id="manual_text" name="manual_text" placeholder="Cole aqui o texto que o chat deverá consultar..." required>{{ old('manual_text') }}</textarea>
                    </div>
                    <button type="submit">Salvar texto e indexar</button>
                </form>
            </div>

            <div class="card">
                <h2>Base atual</h2>

                @if ($documents->isEmpty())
                    <p class="muted">Nenhum documento enviado ainda.</p>
                @else
                    <form id="delete-selected-form" method="POST" action="{{ route('documents.destroy-selected') }}" onsubmit="return confirm('Confirma a exclusão dos documentos selecionados? Esta ação remove os arquivos e a indexação da base.');">
                        @csrf

                        <div class="danger-panel">
                            <p class="muted">Selecione documentos para excluir da base atual. Informe a senha de administrador para confirmar.</p>
                            <div style="display: grid; gap: 12px; grid-template-columns: minmax(180px, 1fr) auto; align-items: end;">
                                <div>
                                    <label for="delete-password">Senha</label>
                                    <input id="delete-password" type="password" name="password" autocomplete="current-password" required>
                                </div>
                                <button class="delete" type="submit">Excluir selecionados</button>
                            </div>
                        </div>

                        <table>
                        <thead>
                            <tr>
                                <th class="select-cell">Sel.</th>
                                <th>Documento</th>
                                <th>Status</th>
                                <th>Chunks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($documents as $document)
                                <tr>
                                    <td class="select-cell"><input type="checkbox" name="documents[]" value="{{ $document->id }}" aria-label="Selecionar {{ $document->original_name }}"></td>
                                    <td>
                                        <strong>{{ $document->title }}</strong><br>
                                        <span class="muted">{{ $document->original_name }} · {{ strtoupper($document->extension) }}</span>
                                        @if ($document->error_message)
                                            <br><span class="muted">{{ $document->error_message }}</span>
                                        @endif
                                    </td>
                                    <td><span class="badge {{ $document->status }}">{{ $document->status }}</span></td>
                                    <td>{{ $document->chunks_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </form>
                @endif
            </div>
        </section>
    </main>
</body>
</html>
