<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso aos documentos DRSP</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #eef3fb;
            --glass: rgba(255, 255, 255, 0.82);
            --border: rgba(148, 163, 184, 0.28);
            --text: #111827;
            --muted: #667085;
            --primary: #0a84ff;
            --danger: #b42318;
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", Inter, ui-sans-serif, system-ui, sans-serif;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            background:
                radial-gradient(circle at 12% 8%, rgba(10, 132, 255, 0.22), transparent 30%),
                radial-gradient(circle at 88% 14%, rgba(175, 82, 222, 0.16), transparent 28%),
                linear-gradient(135deg, #f7faff 0%, #eef3fb 48%, #f8fbff 100%);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
        }

        .modal {
            width: min(100%, 430px);
            border: 1px solid var(--border);
            border-radius: 30px;
            background: var(--glass);
            box-shadow: 0 28px 80px rgba(15, 23, 42, 0.16);
            backdrop-filter: blur(24px) saturate(160%);
            padding: 28px;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 26px;
            letter-spacing: -0.05em;
        }

        p {
            margin: 0 0 22px;
            color: var(--muted);
            line-height: 1.6;
        }

        form {
            display: grid;
            gap: 14px;
        }

        label {
            display: grid;
            gap: 7px;
            color: #1f2937;
            font-weight: 800;
        }

        input {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.88);
            color: var(--text);
            padding: 13px 14px;
            font: inherit;
        }

        input:focus {
            border-color: rgba(10, 132, 255, 0.7);
            box-shadow: 0 0 0 4px rgba(10, 132, 255, 0.12);
            outline: none;
        }

        button, .link {
            border: 0;
            border-radius: 16px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 13px 16px;
            text-decoration: none;
            font-weight: 850;
            font: inherit;
        }

        button {
            margin-top: 4px;
            background: linear-gradient(180deg, #37a1ff 0%, #0a84ff 55%, #0068d9 100%);
            color: #fff;
        }

        .link {
            margin-top: 10px;
            color: var(--primary);
            background: rgba(10, 132, 255, 0.10);
        }

        .error {
            margin-bottom: 16px;
            border-radius: 16px;
            padding: 13px 14px;
            background: rgba(254, 228, 226, 0.9);
            color: var(--danger);
            font-weight: 750;
        }
    </style>
</head>
<body>
    <main class="modal" role="dialog" aria-labelledby="login-title" aria-modal="true">
        <h1 id="login-title">Acesso restrito</h1>
        <p>Informe login e senha para acessar a página de indexação de documentos.</p>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('documents.login.store') }}">
            @csrf
            <label for="username">
                Login
                <input id="username" type="text" name="username" value="{{ old('username') }}" autocomplete="username" autofocus required>
            </label>

            <label for="password">
                Senha
                <input id="password" type="password" name="password" autocomplete="current-password" required>
            </label>

            <button type="submit">Entrar</button>
        </form>

        <a class="link" href="{{ route('chat.index') }}">Voltar ao chat</a>
    </main>
</body>
</html>
