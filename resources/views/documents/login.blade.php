<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso aos documentos DRSP</title>
    <style>
        :root {
            color-scheme: light;
            --brand-blue: #2563eb;
            --brand-purple: #7c3aed;
            --bg: #f4f7ff;
            --surface: rgba(255, 255, 255, 0.88);
            --border: #e2e8f0;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --danger: #dc2626;
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

        .logo .icon {
            width: 30px;
            height: 30px;
        }

        * { box-sizing: border-box; }

        body {
            display: grid;
            min-height: 100vh;
            margin: 0;
            place-items: center;
            padding: 28px;
            background: linear-gradient(135deg, #f8fbff 0%, #eef3ff 48%, #f5f0ff 100%);
            color: var(--text-primary);
            -webkit-font-smoothing: antialiased;
        }

        .login-shell {
            width: min(100%, 430px);
            text-align: center;
        }

        .logo {
            display: grid;
            width: 64px;
            height: 64px;
            margin: 0 auto 18px;
            place-items: center;
            border-radius: 18px;
            background: linear-gradient(135deg, var(--brand-blue), var(--brand-purple));
            box-shadow: 0 18px 36px rgba(79, 70, 229, 0.28);
            color: #ffffff;
            font-size: 30px;
            font-weight: 900;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 29px;
            letter-spacing: -0.055em;
        }

        .subtitle {
            margin: 0 0 34px;
            color: var(--text-secondary);
            font-size: 15px;
        }

        .card {
            border: 1px solid var(--border);
            border-radius: 18px;
            background: var(--surface);
            box-shadow: 0 22px 50px rgba(15, 23, 42, 0.12);
            padding: 28px;
            text-align: left;
        }

        form {
            display: grid;
            gap: 22px;
        }

        label {
            display: grid;
            gap: 10px;
            color: var(--text-primary);
            font-size: 14px;
            font-weight: 800;
        }

        .field {
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            background: #ffffff;
            padding: 0 13px;
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .field:focus-within {
            border-color: rgba(37, 99, 235, 0.62);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
        }

        .field span {
            color: var(--text-secondary);
            font-size: 18px;
        }

        input {
            width: 100%;
            border: 0;
            outline: none;
            background: transparent;
            color: var(--text-primary);
            padding: 13px 0;
            font: inherit;
        }

        input::placeholder { color: #94a3b8; }

        button,
        .link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 9px;
            cursor: pointer;
            padding: 13px 16px;
            text-decoration: none;
            font: inherit;
            font-weight: 850;
        }

        button {
            width: 100%;
            margin-top: 4px;
            background: linear-gradient(135deg, #3b82f6, var(--brand-blue));
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.26);
            color: #ffffff;
        }

        .helper {
            margin: 22px 0 0;
            border-top: 1px solid var(--border);
            padding-top: 22px;
            color: var(--text-secondary);
            font-size: 13px;
            line-height: 1.6;
            text-align: center;
        }

        .helper strong { color: var(--brand-blue); }

        .footer-note {
            margin: 28px 0 0;
            color: var(--text-secondary);
            font-size: 13px;
        }

        .link {
            margin-top: 18px;
            color: var(--brand-blue);
            background: rgba(37, 99, 235, 0.08);
        }

        .error {
            margin-bottom: 18px;
            border: 1px solid #fecaca;
            border-radius: 12px;
            padding: 13px 14px;
            background: #fef2f2;
            color: var(--danger);
            font-weight: 750;
        }
    </style>
</head>
<body>
    <main class="login-shell" role="main" aria-labelledby="login-title">
        <div class="logo"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l1.7 5.2L19 10l-5.3 1.8L12 17l-1.7-5.2L5 10l5.3-1.8L12 3z"/><path d="M5 14l.9 2.6L8.5 18l-2.6.9L5 21l-.9-2.1L1.5 18l2.6-1.4L5 14z"/><path d="M19 3l.8 2.2L22 6l-2.2.8L19 9l-.8-2.2L16 6l2.2-.8L19 3z"/></svg></div>
        <h1 id="login-title">Chat DRSP</h1>
        <p class="subtitle">Acesso restrito para gerenciamento de documentos</p>

        <section class="card" aria-label="Formulário de acesso">
            @if ($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('documents.login.store') }}">
                @csrf
                <label for="username">
                    Administrador Base Interna
                    <div class="field">
                        <span aria-hidden="true"><svg class="icon" viewBox="0 0 24 24"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg></span>
                        <input id="username" type="text" name="username" value="{{ old('username') }}" placeholder="administrador" autocomplete="username" autofocus required>
                    </div>
                </label>

                <label for="password">
                    Senha
                    <div class="field">
                        <span aria-hidden="true"><svg class="icon" viewBox="0 0 24 24"><rect width="14" height="10" x="5" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
                        <input id="password" type="password" name="password" placeholder="••••••••" autocomplete="current-password" required>
                    </div>
                </label>

                <button type="submit"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><rect width="14" height="10" x="5" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> Entrar</button>
            </form>

        </section>

        <a class="link" href="{{ route('chat.index') }}">Voltar ao chat</a>
        <p class="footer-note">Sistema de IA local para consultas DRSP/SUAS</p>
    </main>
</body>
</html>
