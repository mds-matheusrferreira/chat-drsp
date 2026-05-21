<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos DRSP</title>
    <style>
        :root {
            color-scheme: light;
            --brand-blue: #2563eb;
            --brand-purple: #7c3aed;
            --brand-green: #22c55e;
            --bg: #f4f7ff;
            --surface: rgba(255, 255, 255, 0.84);
            --surface-strong: #ffffff;
            --border: #e2e8f0;
            --border-strong: #cbd5e1;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --danger: #dc2626;
            --success: #16a34a;
            --warning: #d97706;
            --shadow: 0 22px 50px rgba(15, 23, 42, 0.10);
            --shadow-soft: 0 10px 28px rgba(15, 23, 42, 0.08);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .icon {
            width: 18px;
            height: 18px;
            flex: 0 0 auto;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            fill: none;
        }

        .brand-mark .icon,
        .page-mark .icon,
        .upload-icon .icon {
            width: 22px;
            height: 22px;
        }

        .upload-icon .icon {
            width: 34px;
            height: 34px;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #f8fbff 0%, #eef3ff 48%, #f5f0ff 100%);
            color: var(--text-primary);
            -webkit-font-smoothing: antialiased;
        }

        button,
        input,
        textarea { font: inherit; }

        a { color: inherit; }

        .app-shell {
            display: grid;
            min-height: 100vh;
            grid-template-columns: 264px minmax(0, 1fr);
        }

        .sidebar {
            display: flex;
            position: sticky;
            top: 0;
            height: 100vh;
            flex-direction: column;
            border-right: 1px solid var(--border);
            background: rgba(248, 250, 252, 0.86);
            backdrop-filter: blur(18px);
        }

        .brand {
            display: flex;
            gap: 14px;
            align-items: center;
            padding: 34px 24px 24px;
        }

        .brand-mark,
        .page-mark,
        .upload-icon {
            display: grid;
            place-items: center;
            color: #ffffff;
            background: linear-gradient(135deg, var(--brand-blue), var(--brand-purple));
            box-shadow: 0 12px 28px rgba(79, 70, 229, 0.28);
        }

        .brand-mark {
            width: 42px;
            height: 42px;
            border-radius: 15px;
            font-size: 22px;
            font-weight: 900;
        }

        .brand h1 {
            margin: 0;
            font-size: 25px;
            line-height: 1;
            letter-spacing: -0.05em;
        }

        .brand p {
            margin: 7px 0 0;
            color: var(--text-secondary);
            font-size: 13px;
        }

        .nav {
            display: grid;
            gap: 4px;
            padding: 18px 14px;
            border-top: 1px solid rgba(226, 232, 240, 0.7);
        }

        .nav a,
        .nav button {
            display: flex;
            gap: 12px;
            align-items: center;
            width: 100%;
            border: 0;
            border-radius: 12px;
            background: transparent;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 11px 12px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 750;
            text-align: left;
        }

        .nav a:hover,
        .nav button:hover,
        .nav .active {
            background: rgba(37, 99, 235, 0.08);
            color: var(--text-primary);
        }

        .recent {
            padding: 0 20px;
        }

        .recent-title {
            margin: 12px 0;
            color: var(--text-secondary);
            font-size: 12px;
            font-weight: 850;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .recent-list {
            display: grid;
            gap: 10px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .recent-list li {
            color: #475569;
            font-size: 14px;
            font-weight: 700;
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 18px 14px 22px;
            border-top: 1px solid var(--border);
        }

        .main {
            min-width: 0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.70) 0%, rgba(239, 243, 255, 0.74) 100%);
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 76px;
            padding: 0 32px;
            border-bottom: 1px solid var(--border);
            background: rgba(255, 255, 255, 0.76);
            backdrop-filter: blur(18px);
        }

        .page-title {
            display: flex;
            gap: 14px;
            align-items: center;
        }

        .page-mark {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            font-size: 20px;
        }

        h1,
        h2,
        h3,
        p { margin-top: 0; }

        h1 {
            margin: 0 0 4px;
            font-size: 24px;
            letter-spacing: -0.045em;
        }

        .subtitle,
        .muted {
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .subtitle {
            margin: 0;
            font-size: 13px;
        }

        .top-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .link,
        button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 0;
            border-radius: 10px;
            cursor: pointer;
            padding: 11px 16px;
            text-decoration: none;
            font-weight: 850;
        }

        .link {
            background: transparent;
            color: var(--text-secondary);
        }

        .primary-button,
        button[type="submit"] {
            background: linear-gradient(135deg, #3b82f6, var(--brand-blue));
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.22);
            color: #ffffff;
        }

        .secondary {
            background: rgba(37, 99, 235, 0.09);
            color: var(--brand-blue);
            box-shadow: none;
        }

        .delete {
            background: #fef2f2;
            color: var(--danger);
            box-shadow: none;
        }

        .content {
            display: grid;
            grid-template-columns: minmax(0, 780px) 360px;
            gap: 26px;
            width: min(100%, 1420px);
            margin: 0 auto;
            padding: 28px 24px 56px;
            align-items: start;
        }

        .document-base-card {
            grid-column: 1 / -1;
            grid-row: 3;
        }

        .left-column {
            display: contents;
        }

        .right-column {
            display: grid;
            grid-column: 2;
            grid-row: 1 / 3;
            gap: 24px;
            min-width: 0;
            align-self: start;
        }

        .left-column > .alert {
            grid-column: 1 / -1;
        }

        .left-column > .card:not(.document-base-card) {
            grid-column: 1;
        }

        .card,
        .side-card {
            min-width: 0;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: var(--surface);
            box-shadow: var(--shadow-soft);
            padding: 26px;
        }

        .card h2,
        .side-card h3 {
            margin: 0 0 10px;
            letter-spacing: -0.035em;
        }

        .card h2 { font-size: 19px; }
        .side-card h3 { font-size: 16px; }

        form { display: grid; gap: 16px; }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-primary);
            font-size: 14px;
            font-weight: 850;
        }

        input[type="text"],
        input[type="password"],
        textarea {
            width: 100%;
            border: 1px solid var(--border-strong);
            border-radius: 10px;
            outline: none;
            background: #ffffff;
            color: var(--text-primary);
            padding: 13px 14px;
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }

        input:focus,
        textarea:focus {
            border-color: rgba(37, 99, 235, 0.58);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
        }

        textarea {
            min-height: 206px;
            resize: vertical;
            line-height: 1.65;
        }

        .dropzone {
            display: grid;
            min-height: 234px;
            place-items: center;
            gap: 12px;
            border: 1.5px dashed #cbd5e1;
            border-radius: 22px;
            background: rgba(248, 250, 252, 0.62);
            padding: 28px;
            cursor: pointer;
            text-align: center;
            transition: border-color 0.2s ease, background 0.2s ease, transform 0.2s ease;
        }

        .dropzone.drag-over {
            border-color: var(--brand-blue);
            background: rgba(37, 99, 235, 0.08);
            transform: translateY(-1px);
        }

        .dropzone input[type="file"] {
            position: absolute;
            inline-size: 1px;
            block-size: 1px;
            opacity: 0;
            pointer-events: none;
        }

        .upload-icon {
            width: 68px;
            height: 68px;
            border-radius: 18px;
            background: rgba(37, 99, 235, 0.10);
            box-shadow: none;
            color: var(--brand-blue);
            font-size: 34px;
        }

        .dropzone-title {
            display: block;
            color: var(--text-primary);
            font-size: 17px;
            font-weight: 900;
        }

        .file-hint {
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            background: rgba(239, 246, 255, 0.82);
            color: var(--text-secondary);
            padding: 12px 14px;
            font-size: 13px;
        }

        .file-list {
            display: grid;
            width: 100%;
            gap: 8px;
            margin: 0;
            padding: 0;
            list-style: none;
            text-align: left;
        }

        .file-list li {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            border-radius: 10px;
            background: #ffffff;
            padding: 8px 10px;
            color: var(--text-secondary);
            font-size: 13px;
        }

        .file-feedback {
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .file-feedback.invalid {
            color: var(--danger);
            font-weight: 800;
        }

        .text-counter {
            margin-top: 8px;
            color: var(--text-secondary);
            font-size: 12px;
            font-weight: 750;
        }

        .alert {
            border: 1px solid #bbf7d0;
            border-radius: 14px;
            background: #f0fdf4;
            color: var(--success);
            padding: 13px 14px;
            font-weight: 750;
        }

        .error {
            border-color: #fecaca;
            background: #fef2f2;
            color: var(--danger);
        }

        .steps {
            display: grid;
            gap: 18px;
            margin: 22px 0 0;
        }

        .step {
            display: grid;
            grid-template-columns: 28px 1fr;
            gap: 12px;
        }

        .step-number {
            display: grid;
            width: 28px;
            height: 28px;
            place-items: center;
            border-radius: 999px;
            background: #dbeafe;
            color: var(--brand-blue);
            font-size: 13px;
            font-weight: 900;
        }

        .step strong { display: block; margin-bottom: 2px; }
        .step span { color: var(--text-secondary); font-size: 13px; line-height: 1.5; }

        .privacy {
            border-color: #bbf7d0;
            background: linear-gradient(135deg, rgba(240, 253, 244, 0.90), rgba(224, 242, 254, 0.72));
        }

        .danger-panel {
            margin-bottom: 18px;
            border: 1px solid #fecaca;
            border-radius: 16px;
            background: rgba(254, 242, 242, 0.76);
            padding: 16px;
        }

        .danger-actions {
            display: grid;
            grid-template-columns: minmax(180px, 1fr) auto;
            gap: 12px;
            align-items: end;
        }

        .table-wrap {
            max-width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            padding-bottom: 8px;
            -webkit-overflow-scrolling: touch;
        }

        .table-wrap::-webkit-scrollbar {
            height: 10px;
        }

        .table-wrap::-webkit-scrollbar-track {
            border-radius: 999px;
            background: #e2e8f0;
        }

        .table-wrap::-webkit-scrollbar-thumb {
            border-radius: 999px;
            background: #94a3b8;
        }

        table {
            width: 100%;
            min-width: 980px;
            border-collapse: collapse;
        }

        th,
        td {
            border-bottom: 1px solid rgba(226, 232, 240, 0.9);
            padding: 13px 10px;
            text-align: left;
            vertical-align: top;
        }

        th {
            color: var(--text-secondary);
            font-size: 12px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .badge {
            display: inline-flex;
            border-radius: 999px;
            background: #dbeafe;
            color: var(--brand-blue);
            padding: 5px 9px;
            font-size: 12px;
            font-weight: 850;
        }

        .badge.ready { background: #dcfce7; color: var(--success); }
        .badge.failed { background: #fee2e2; color: var(--danger); }
        .badge.indexing { background: #fef3c7; color: var(--warning); }

        [data-document-title],
        [data-document-name] {
            overflow-wrap: anywhere;
            word-break: normal;
        }

        .document-error {
            display: block;
            margin-top: 8px;
            border-radius: 10px;
            background: #fef2f2;
            color: var(--danger);
            padding: 8px 10px;
            line-height: 1.5;
        }

        .actions {
            display: flex;
            flex-wrap: nowrap;
            gap: 8px;
            min-width: 132px;
        }

        .actions .secondary {
            min-width: 116px;
            white-space: nowrap;
        }

        .select-cell { width: 42px; }

        th:nth-child(2),
        td:nth-child(2) {
            min-width: 0;
            width: auto;
        }

        th:nth-child(3),
        td:nth-child(3),
        th:nth-child(4),
        td:nth-child(4) {
            white-space: nowrap;
        }

        th:nth-child(5),
        td:nth-child(5) {
            width: 170px;
            min-width: 170px;
            padding-right: 20px;
            white-space: nowrap;
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--danger);
        }

        @media (max-width: 1120px) {
            .content { grid-template-columns: 1fr; }
            .left-column,
            .right-column {
                display: grid;
                gap: 24px;
                min-width: 0;
            }
            .left-column > .alert,
            .left-column > .card:not(.document-base-card),
            .right-column,
            .document-base-card {
                grid-column: auto;
                grid-row: auto;
            }
            .right-column { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 820px) {
            .app-shell { display: block; }
            .sidebar { position: static; height: auto; }
            .recent, .sidebar-footer { display: none; }
            .nav { grid-template-columns: repeat(3, 1fr); }
            .brand { padding: 20px; }
            .topbar { align-items: flex-start; flex-direction: column; gap: 14px; padding: 18px 20px; }
            .content { padding: 22px 16px 42px; }
            .right-column { grid-template-columns: 1fr; }
            .danger-actions { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-mark"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l1.7 5.2L19 10l-5.3 1.8L12 17l-1.7-5.2L5 10l5.3-1.8L12 3z"/><path d="M5 14l.9 2.6L8.5 18l-2.6.9L5 21l-.9-2.1L1.5 18l2.6-1.4L5 14z"/><path d="M19 3l.8 2.2L22 6l-2.2.8L19 9l-.8-2.2L16 6l2.2-.8L19 3z"/></svg></div>
                <div>
                    <h1>Chat DRSP</h1>
                    <p>AI Assistant</p>
                </div>
            </div>

            <nav class="nav" aria-label="Navegação principal">
                <a href="{{ route('chat.index') }}"><span><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg></span> Nova Conversa</a>
                <a class="active" href="{{ route('documents.index') }}"><span><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg></span> Documentos</a>
            </nav>

            <div class="sidebar-footer nav">
                <a href="{{ route('chat.index') }}"><span><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.38a2 2 0 0 0-.73-2.73l-.15-.09a2 2 0 0 1-1-1.74v-.51a2 2 0 0 1 1-1.72l.15-.1a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg></span> Configurações</a>
            </div>
        </aside>

        <main class="main">
            <header class="topbar">
                <div class="page-title">
                    <div class="page-mark"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg></div>
                    <div>
                        <h1>Documentos Internos</h1>
                        <p class="subtitle">Gerencie a base de conhecimento do chat</p>
                    </div>
                </div>
                <div class="top-actions">
                    <form method="POST" action="{{ route('documents.logout') }}">
                        @csrf
                        <button class="secondary" type="submit"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg> Sair</button>
                    </form>
                    <a class="link" href="{{ route('chat.index') }}"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg> Fechar</a>
                </div>
            </header>

            <section class="content">
                <div class="left-column">
                    @if (session('status'))
                        <div class="alert">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert error">{{ $errors->first() }}</div>
                    @endif

                    <section class="card">
                        <h2>Enviar novos documentos</h2>
                        <p class="muted">Adicione novos documentos à base de conhecimento. Os documentos serão indexados e estarão disponíveis para consulta.</p>

                        <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
                            @csrf
                            <label class="dropzone" id="document-dropzone" for="documents">
                                <input id="documents" type="file" name="documents[]" accept="{{ collect($allowedExtensions)->map(fn ($extension) => '.'.$extension)->implode(',') }}" multiple required>
                                <span class="upload-icon"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/></svg></span>
                                <span>
                                    <span class="dropzone-title">Arraste arquivos aqui</span>
                                    <span class="muted">ou clique para selecionar</span>
                                </span>
                                <ul id="selected-files" class="file-list" hidden></ul>
                                <span id="file-feedback" class="file-feedback">Nenhum arquivo selecionado.</span>
                            </label>

                            <div class="file-hint"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg> Formatos aceitos: {{ implode(', ', $allowedExtensions) }}. Tamanho máximo: {{ $maxUploadMb }} MB por arquivo.</div>
                            <button id="upload-submit" class="primary-button" type="submit"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/></svg> Enviar e indexar documentos</button>
                        </form>
                    </section>

                    <section class="card">
                        <h2>Colar texto diretamente</h2>
                        <p class="muted">Cole texto de documentos para adicionar à base de conhecimento sem fazer upload de arquivo.</p>

                        <form method="POST" action="{{ route('documents.text.store') }}">
                            @csrf
                            <div>
                                <label for="manual_title">Título do documento</label>
                                <input id="manual_title" type="text" name="manual_title" placeholder="Ex.: Procedimentos Internos DRSP" value="{{ old('manual_title') }}" required>
                            </div>
                            <div>
                                <label for="manual_text">Conteúdo do texto</label>
                                <textarea id="manual_text" name="manual_text" placeholder="Cole aqui o conteúdo do documento..." required>{{ old('manual_text') }}</textarea>
                                <div class="text-counter"><span id="manual-text-count">0</span> caracteres</div>
                            </div>
                            <button type="submit" style="background: linear-gradient(135deg, #a78bfa, var(--brand-purple)); box-shadow: 0 12px 24px rgba(124, 58, 237, 0.22);"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg> Salvar texto na base</button>
                        </form>
                    </section>

                    <section class="card document-base-card">
                        <h2>Base atual</h2>

                        @if ($documents->isEmpty())
                            <p class="muted">Nenhum documento enviado ainda.</p>
                        @else
                            <form id="delete-selected-form" method="POST" action="{{ route('documents.destroy-selected') }}" onsubmit="return confirm('Confirma a exclusão dos documentos selecionados? Esta ação remove os arquivos e a indexação da base.');">
                                @csrf

                                <div class="danger-panel">
                                    <p class="muted">Selecione documentos para excluir da base atual. Informe a senha de administrador para confirmar.</p>
                                    <div class="danger-actions">
                                        <div>
                                            <label for="delete-password">Senha</label>
                                            <input id="delete-password" type="password" name="password" autocomplete="current-password" required>
                                        </div>
                                        <button class="delete" type="submit">Excluir selecionados</button>
                                    </div>
                                </div>

                                <div class="table-wrap">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th class="select-cell">Sel.</th>
                                                <th>Documento</th>
                                                <th>Status</th>
                                                <th>Chunks</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($documents as $document)
                                                <tr data-document-id="{{ $document->id }}">
                                                    <td class="select-cell"><input type="checkbox" name="documents[]" value="{{ $document->id }}" aria-label="Selecionar {{ $document->original_name }}"></td>
                                                    <td>
                                                        <strong data-document-title>{{ $document->title }}</strong><br>
                                                        <span class="muted" data-document-name>{{ $document->original_name }} · {{ strtoupper($document->extension) }}</span>
                                                        <span class="document-error" data-document-error @if (! $document->error_message) hidden @endif>{{ $document->error_message }}</span>
                                                    </td>
                                                    <td><span data-document-status class="badge {{ $document->status }}">{{ $document->status }}</span></td>
                                                    <td data-document-chunks>{{ $document->chunks_count }}</td>
                                                    <td>
                                                        <div class="actions">
                                                            <button class="secondary" type="submit" form="reprocess-document-{{ $document->id }}" data-reprocess-button @if ($document->status === 'indexing') hidden @endif>Processar</button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </form>

                            @foreach ($documents as $document)
                                <form id="reprocess-document-{{ $document->id }}" method="POST" action="{{ route('documents.reprocess', $document) }}">
                                    @csrf
                                </form>
                            @endforeach
                        @endif
                    </section>
                </div>

                <aside class="right-column">
                    <section class="side-card">
                        <h3>Como funciona</h3>
                        <div class="steps">
                            <div class="step">
                                <span class="step-number">1</span>
                                <div><strong>Upload</strong><span>Envie seus documentos PDF ou texto.</span></div>
                            </div>
                            <div class="step">
                                <span class="step-number">2</span>
                                <div><strong>Indexação</strong><span>O conteúdo é processado e indexado.</span></div>
                            </div>
                            <div class="step">
                                <span class="step-number">3</span>
                                <div><strong>Consulta</strong><span>A IA usa os docs para responder.</span></div>
                            </div>
                        </div>
                    </section>

                    <section class="side-card privacy">
                        <h3><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.7 8.9a1 1 0 0 1-.6 0C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.2-2.7a1.2 1.2 0 0 1 1.6 0C14.5 3.8 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg> Privacidade garantida</h3>
                        <p class="muted">Todos os documentos são processados localmente. Nenhum dado é enviado para servidores externos.</p>
                    </section>
                </aside>
            </section>
        </main>
    </div>

    <script>
        const allowedExtensions = @json($allowedExtensions);
        const maxUploadBytes = @json($maxUploadMb * 1024 * 1024);
        const uploadForm = document.querySelector('form[action="{{ route('documents.store') }}"]');
        const fileInput = document.getElementById('documents');
        const dropzone = document.getElementById('document-dropzone');
        const fileList = document.getElementById('selected-files');
        const fileFeedback = document.getElementById('file-feedback');
        const uploadSubmit = document.getElementById('upload-submit');
        const manualText = document.getElementById('manual_text');
        const manualTextCount = document.getElementById('manual-text-count');

        function formatBytes(bytes) {
            if (bytes < 1024 * 1024) {
                return `${Math.max(1, Math.round(bytes / 1024))} KB`;
            }

            return `${(bytes / 1024 / 1024).toFixed(1)} MB`;
        }

        function renderSelectedFiles() {
            const files = Array.from(fileInput.files || []);
            fileList.innerHTML = '';

            if (files.length === 0) {
                fileList.hidden = true;
                fileFeedback.textContent = 'Nenhum arquivo selecionado.';
                fileFeedback.classList.remove('invalid');
                return;
            }

            const invalidFiles = [];

            files.forEach((file) => {
                const extension = file.name.split('.').pop().toLowerCase();
                const invalidExtension = !allowedExtensions.includes(extension);
                const invalidSize = file.size > maxUploadBytes;

                if (invalidExtension || invalidSize) {
                    invalidFiles.push(file.name);
                }

                const item = document.createElement('li');
                const name = document.createElement('span');
                const size = document.createElement('span');

                name.textContent = file.name;
                size.textContent = formatBytes(file.size);

                if (invalidExtension || invalidSize) {
                    name.style.color = 'var(--danger)';
                    name.style.fontWeight = '800';
                }

                item.append(name, size);
                fileList.append(item);
            });

            fileList.hidden = false;

            if (invalidFiles.length > 0) {
                fileFeedback.textContent = 'Há arquivos com formato ou tamanho inválido. Corrija antes de enviar.';
                fileFeedback.classList.add('invalid');
                return;
            }

            fileFeedback.textContent = `${files.length} arquivo(s) pronto(s) para envio.`;
            fileFeedback.classList.remove('invalid');
        }

        function updateManualTextCount() {
            if (manualText && manualTextCount) {
                manualTextCount.textContent = manualText.value.length;
            }
        }

        fileInput?.addEventListener('change', renderSelectedFiles);
        manualText?.addEventListener('input', updateManualTextCount);
        updateManualTextCount();

        ['dragenter', 'dragover'].forEach((eventName) => {
            dropzone?.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropzone.classList.add('drag-over');
            });
        });

        ['dragleave', 'drop'].forEach((eventName) => {
            dropzone?.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropzone.classList.remove('drag-over');
            });
        });

        dropzone?.addEventListener('drop', (event) => {
            const files = Array.from(event.dataTransfer.files || []);
            const transfer = new DataTransfer();

            files.forEach((file) => transfer.items.add(file));
            fileInput.files = transfer.files;
            renderSelectedFiles();
        });

        uploadForm?.addEventListener('submit', () => {
            uploadSubmit.disabled = true;
            uploadSubmit.textContent = 'Enviando...';
        });

        const statusUrl = @json(route('documents.status'));
        let pollingTimer = null;

        function updateDocumentRow(item) {
            const row = document.querySelector(`[data-document-id="${item.id}"]`);

            if (!row) {
                return;
            }

            const status = row.querySelector('[data-document-status]');
            const chunks = row.querySelector('[data-document-chunks]');
            const error = row.querySelector('[data-document-error]');
            const reprocessButton = row.querySelector('[data-reprocess-button]');

            status.textContent = item.status;
            status.className = `badge ${item.status}`;
            chunks.textContent = item.chunks_count;
            error.textContent = item.error_message || '';
            error.hidden = !item.error_message;
            reprocessButton.hidden = item.status === 'indexing';
        }

        async function refreshDocumentStatus() {
            try {
                const response = await fetch(statusUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();
                const documents = Array.isArray(payload.documents) ? payload.documents : [];

                documents.forEach(updateDocumentRow);

                if (!documents.some((document) => document.status === 'indexing') && pollingTimer) {
                    clearInterval(pollingTimer);
                    pollingTimer = null;
                }
            } catch (error) {
            }
        }

        if (document.querySelector('.badge.indexing')) {
            pollingTimer = setInterval(refreshDocumentStatus, 4000);
        }
    </script>
</body>
</html>
