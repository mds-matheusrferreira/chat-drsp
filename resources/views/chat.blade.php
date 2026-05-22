<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chat DRSP</title>
    <style>
        :root {
            color-scheme: light;
            --brand-blue: #2563eb;
            --brand-purple: #7c3aed;
            --brand-green: #22c55e;
            --bg: #f4f7ff;
            --bg-soft: #eef2ff;
            --surface: rgba(255, 255, 255, 0.82);
            --surface-strong: #ffffff;
            --border: #e2e8f0;
            --border-strong: #cbd5e1;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --danger: #dc2626;
            --danger-soft: #fef2f2;
            --app-bg: linear-gradient(135deg, #f8fbff 0%, #eef3ff 48%, #f5f0ff 100%);
            --sidebar-bg: rgba(248, 250, 252, 0.86);
            --main-bg: linear-gradient(180deg, rgba(255, 255, 255, 0.72) 0%, rgba(239, 243, 255, 0.72) 100%);
            --topbar-bg: rgba(255, 255, 255, 0.74);
            --right-bg: rgba(248, 250, 252, 0.64);
            --input-bg: #ffffff;
            --subtle-bg: #f8fafc;
            --blue-soft: #dbeafe;
            --shadow: 0 22px 50px rgba(15, 23, 42, 0.10);
            --shadow-soft: 0 10px 28px rgba(15, 23, 42, 0.08);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        :root[data-theme="dark"] {
            color-scheme: dark;
            --bg: #08111f;
            --bg-soft: #111827;
            --surface: rgba(15, 23, 42, 0.88);
            --surface-strong: #0f172a;
            --border: #263447;
            --border-strong: #334155;
            --text-primary: #e5eefc;
            --text-secondary: #9fb0c8;
            --text-muted: #64748b;
            --danger: #f87171;
            --danger-soft: rgba(127, 29, 29, 0.22);
            --app-bg: linear-gradient(135deg, #07111f 0%, #111827 48%, #1e1635 100%);
            --sidebar-bg: rgba(15, 23, 42, 0.90);
            --main-bg: linear-gradient(180deg, rgba(15, 23, 42, 0.82) 0%, rgba(17, 24, 39, 0.88) 100%);
            --topbar-bg: rgba(15, 23, 42, 0.78);
            --right-bg: rgba(15, 23, 42, 0.64);
            --input-bg: #0f172a;
            --subtle-bg: #111827;
            --blue-soft: rgba(37, 99, 235, 0.22);
            --shadow: 0 22px 50px rgba(0, 0, 0, 0.35);
            --shadow-soft: 0 10px 28px rgba(0, 0, 0, 0.28);
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            margin: 0;
            background: var(--app-bg);
            color: var(--text-primary);
            -webkit-font-smoothing: antialiased;
        }

        button,
        textarea { font: inherit; }

        button { -webkit-tap-highlight-color: transparent; }

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

        .icon-sm {
            width: 15px;
            height: 15px;
        }

        .icon-lg {
            width: 42px;
            height: 42px;
        }

        .brand-mark .icon,
        .hero-mark .icon,
        .panel-icon .icon,
        .settings-icon .icon {
            width: 20px;
            height: 20px;
        }

        .hero-mark .icon {
            width: 42px;
            height: 42px;
        }

        a { color: inherit; }

        [hidden] {
            display: none !important;
        }

        .app-shell {
            display: grid;
            min-height: 100vh;
            grid-template-columns: 264px minmax(0, 1fr) 320px;
        }

        .sidebar {
            display: flex;
            position: sticky;
            top: 0;
            height: 100vh;
            flex-direction: column;
            border-right: 1px solid var(--border);
            background: var(--sidebar-bg);
            backdrop-filter: blur(18px);
        }

        .brand {
            display: flex;
            gap: 14px;
            align-items: center;
            padding: 34px 24px 24px;
        }

        .brand-mark,
        .hero-mark,
        .panel-icon {
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

        .brand-logo {
            display: block;
            width: 180px;
            height: auto;
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
            transition: background 0.18s ease, color 0.18s ease;
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
            margin: 12px 0 12px;
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

        .recent-list button {
            width: 100%;
            border: 0;
            background: transparent;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 2px 0;
            font-size: 14px;
            font-weight: 700;
            text-align: left;
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 18px 14px 22px;
            border-top: 1px solid var(--border);
        }

        .main {
            display: grid;
            min-width: 0;
            grid-template-rows: auto minmax(0, 1fr) auto;
            border-right: 1px solid var(--border);
            background: var(--main-bg);
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 74px;
            padding: 0 32px;
            border-bottom: 1px solid var(--border);
            background: var(--topbar-bg);
            backdrop-filter: blur(18px);
        }

        .topbar-title {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .topbar h2 {
            margin: 0;
            font-size: 20px;
            letter-spacing: -0.04em;
        }

        .status {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            border: 1px solid #bbf7d0;
            border-radius: 999px;
            background: #dcfce7;
            color: #16a34a;
            padding: 7px 12px;
            font-size: 12px;
            font-weight: 850;
        }

        .dot {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: #94a3b8;
            box-shadow: 0 0 0 4px rgba(148, 163, 184, 0.14);
        }

        .status.ok .dot {
            background: var(--brand-green);
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.14), 0 0 16px rgba(34, 197, 94, 0.42);
        }

        .status.error {
            border-color: #fecaca;
            background: #fef2f2;
            color: var(--danger);
        }

        .status.error .dot {
            background: var(--danger);
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.12);
        }

        .status.checking {
            border-color: #fde68a;
            background: #fffbeb;
            color: #b45309;
        }

        .status.checking .dot {
            background: #f59e0b;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.13);
        }

        .ghost-button {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            border: 0;
            background: transparent;
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 14px;
            font-weight: 750;
        }

        .conversation {
            display: block;
            min-height: 0;
            padding: 32px;
            overflow: auto;
        }

        .conversation.is-settings {
            align-content: start;
        }

        .settings-screen {
            width: min(100%, 860px);
            margin: 0 auto;
        }

        .settings-card {
            border: 1px solid var(--border);
            border-radius: 18px;
            background: var(--surface);
            box-shadow: var(--shadow-soft);
            padding: 24px;
        }

        :root[data-theme="dark"] .settings-icon,
        :root[data-theme="dark"] .panel-icon,
        :root[data-theme="dark"] .hero-mark {
            background: rgba(59, 130, 246, 0.18);
            color: #93c5fd;
        }

        :root[data-theme="dark"] .status.ok,
        :root[data-theme="dark"] .connected-badge {
            border-color: rgba(34, 197, 94, 0.34);
            background: rgba(34, 197, 94, 0.14);
            color: #86efac;
        }

        :root[data-theme="dark"] .status.error,
        :root[data-theme="dark"] .error {
            border-color: rgba(248, 113, 113, 0.38);
            background: rgba(127, 29, 29, 0.28);
            color: #fca5a5;
        }

        :root[data-theme="dark"] .status.checking {
            border-color: rgba(251, 191, 36, 0.38);
            background: rgba(120, 53, 15, 0.28);
            color: #fcd34d;
        }

        :root[data-theme="dark"] .settings-badge,
        :root[data-theme="dark"] .pill {
            background: rgba(59, 130, 246, 0.18);
            color: #93c5fd;
        }

        :root[data-theme="dark"] .settings-badge-muted {
            background: var(--subtle-bg);
            color: var(--text-secondary);
        }

        :root[data-theme="dark"] .answer {
            background: var(--surface);
            color: var(--text-primary);
        }

        :root[data-theme="dark"] .source-name,
        :root[data-theme="dark"] .answer-title,
        :root[data-theme="dark"] .sources-summary {
            color: #93c5fd;
        }

        :root[data-theme="dark"] .source-item {
            border-color: var(--border);
        }

        :root[data-theme="dark"] .recent-list button,
        :root[data-theme="dark"] .question-list button {
            color: var(--text-secondary);
        }

        :root[data-theme="dark"] .logout-mini {
            border-color: rgba(248, 113, 113, 0.38);
            background: rgba(127, 29, 29, 0.20);
            color: #fca5a5;
        }

        .settings-card + .settings-card {
            margin-top: 24px;
        }

        .settings-head {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            margin-bottom: 22px;
        }

        .settings-icon {
            display: grid;
            width: 34px;
            height: 34px;
            flex: 0 0 auto;
            place-items: center;
            border-radius: 11px;
            background: rgba(37, 99, 235, 0.10);
            color: var(--brand-blue);
            font-weight: 900;
        }

        .settings-card h3 {
            margin: 0 0 7px;
            font-size: 17px;
            letter-spacing: -0.03em;
        }

        .settings-card p {
            margin: 0;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .settings-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-top: 20px;
        }

        .settings-label {
            display: block;
            margin-bottom: 4px;
            font-weight: 850;
        }

        .switch {
            position: relative;
            width: 36px;
            height: 20px;
            border: 0;
            border-radius: 999px;
            background: #d1d5db;
            padding: 0;
            box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.06);
            cursor: pointer;
        }

        .switch.is-on {
            background: var(--brand-blue);
        }

        .switch.is-on::after {
            transform: translateX(16px);
        }

        .switch::after {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 14px;
            height: 14px;
            border-radius: 999px;
            background: #ffffff;
            content: "";
            transition: transform 0.18s ease;
        }

        .settings-login-note {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 18px !important;
            color: var(--text-secondary) !important;
            font-size: 14px;
            font-weight: 750;
        }

        .settings-action {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            margin-top: 20px;
            border-radius: 10px;
            background: linear-gradient(135deg, #3b82f6, var(--brand-blue));
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.22);
            color: #ffffff;
            padding: 11px 15px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 850;
        }

        .settings-badge {
            display: inline-flex;
            border-radius: 999px;
            background: var(--blue-soft);
            color: var(--brand-blue);
            padding: 8px 12px;
            font-size: 13px;
            font-weight: 850;
        }

        .settings-badge-muted {
            background: var(--subtle-bg);
            color: var(--text-secondary);
        }

        .session-card {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
        }

        .connected-badge {
            border: 1px solid #86efac;
            border-radius: 999px;
            background: #dcfce7;
            color: #16a34a;
            padding: 5px 11px;
            font-size: 12px;
            font-weight: 850;
        }

        .logout-mini {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            margin-top: 22px;
            border: 1px solid #fecaca;
            border-radius: 10px;
            background: #fff;
            color: var(--danger);
            cursor: pointer;
            padding: 9px 13px;
            font-weight: 850;
        }

        .empty-state {
            max-width: 520px;
            margin: 0 auto;
            text-align: center;
        }

        .hero-mark {
            width: 82px;
            height: 82px;
            margin: 0 auto 26px;
            border-radius: 22px;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.12), rgba(124, 58, 237, 0.20));
            color: var(--brand-blue);
            box-shadow: none;
            font-size: 42px;
        }

        .empty-state h3 {
            margin: 0 0 12px;
            font-size: 22px;
            letter-spacing: -0.04em;
        }

        .empty-state p {
            margin: 0;
            color: var(--text-secondary);
            font-size: 16px;
            line-height: 1.65;
        }

        .response-stack {
            display: grid;
            width: min(100%, 920px);
            margin: 0 auto;
            gap: 18px;
        }

        .message-row {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            width: 100%;
        }

        .message-row.user {
            justify-content: flex-end;
        }

        .message-row.assistant {
            justify-content: flex-start;
        }

        .message-avatar {
            display: grid;
            width: 34px;
            height: 34px;
            flex: 0 0 auto;
            place-items: center;
            border-radius: 12px;
            background: rgba(37, 99, 235, 0.12);
            color: var(--brand-blue);
        }

        .message-row.user .message-avatar {
            order: 2;
            background: linear-gradient(135deg, #3b82f6, var(--brand-purple));
            color: #ffffff;
        }

        .message-bubble {
            max-width: min(680px, 78%);
            border: 1px solid var(--border);
            border-radius: 20px;
            background: var(--surface);
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.07);
            padding: 16px 18px;
            color: var(--text-primary);
            line-height: 1.7;
            white-space: pre-wrap;
        }

        .message-row.user .message-bubble {
            border-color: rgba(37, 99, 235, 0.20);
            border-bottom-right-radius: 8px;
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            color: #ffffff;
            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.22);
        }

        .message-row.assistant .message-bubble {
            border-bottom-left-radius: 8px;
        }

        .message-label {
            display: block;
            margin-bottom: 6px;
            color: var(--text-secondary);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            white-space: normal;
        }

        .message-row.user .message-label {
            color: rgba(255, 255, 255, 0.78);
        }

        .message-sources {
            width: min(680px, 78%);
            margin-left: 46px;
        }

        .alert {
            margin-top: 18px;
            border-radius: 20px;
            padding: 18px;
            line-height: 1.75;
            white-space: pre-wrap;
        }

        .answer {
            border: 1px solid rgba(37, 99, 235, 0.16);
            background: var(--surface);
            box-shadow: var(--shadow-soft);
            color: var(--text-primary);
        }

        .answer-title,
        .sources-summary {
            display: inline-flex;
            align-items: center;
            margin-bottom: 10px;
            color: var(--brand-blue);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.11em;
            text-transform: uppercase;
        }

        .answer-title::before,
        .sources-summary::before {
            width: 8px;
            height: 8px;
            margin-right: 8px;
            border-radius: 999px;
            background: var(--brand-blue);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
            content: "";
        }

        .sources-disclosure { margin-top: 14px; }

        .sources-summary {
            cursor: pointer;
            margin-bottom: 0;
        }

        .sources-disclosure[open] .sources-summary { margin-bottom: 12px; }

        .sources-summary::after {
            margin-left: 8px;
            color: var(--text-secondary);
            content: "▾";
            font-size: 11px;
            transition: transform 0.18s ease;
        }

        .sources-disclosure[open] .sources-summary::after { transform: rotate(180deg); }

        .sources-summary::marker,
        .sources-summary::-webkit-details-marker { display: none; content: ""; }

        .source-list {
            display: grid;
            gap: 12px;
            margin: 10px 0 0;
            padding: 0;
            list-style: none;
            white-space: normal;
        }

        .source-item {
            border: 1px solid rgba(37, 99, 235, 0.14);
            border-radius: 16px;
            padding: 12px;
            background: var(--subtle-bg);
        }

        .source-name {
            display: block;
            margin-bottom: 6px;
            color: var(--brand-blue);
            font-weight: 850;
        }

        .source-excerpt { color: var(--text-secondary); line-height: 1.6; }

        .source-meta {
            margin-top: 6px;
            color: var(--text-secondary);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .error {
            border: 1px solid #fecaca;
            background: var(--danger-soft);
            color: var(--danger);
            font-weight: 750;
        }

        .composer-wrap {
            padding: 24px 32px 28px;
            border-top: 1px solid var(--border);
            background: var(--topbar-bg);
        }

        .composer {
            position: relative;
            width: min(100%, 920px);
            margin: 0 auto;
        }

        textarea {
            width: 100%;
            min-height: 124px;
            padding: 18px 150px 48px 18px;
            resize: vertical;
            border: 1px solid var(--border-strong);
            border-radius: 18px;
            outline: none;
            background: var(--input-bg);
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.08);
            color: var(--text-primary);
            font-size: 15px;
            line-height: 1.6;
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }

        textarea:focus {
            border-color: rgba(37, 99, 235, 0.55);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10), 0 12px 32px rgba(15, 23, 42, 0.08);
        }

        textarea::placeholder { color: var(--text-muted); }

        .composer-meta {
            display: flex;
            position: absolute;
            right: 14px;
            bottom: 14px;
            gap: 12px;
            align-items: center;
        }

        .counter {
            color: var(--text-secondary);
            font-size: 12px;
            font-weight: 750;
        }

        .submit {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            border: 0;
            border-radius: 11px;
            background: linear-gradient(135deg, #60a5fa, var(--brand-blue));
            box-shadow: 0 10px 22px rgba(37, 99, 235, 0.24);
            color: #ffffff;
            cursor: pointer;
            padding: 11px 16px;
            font-size: 13px;
            font-weight: 850;
        }

        .submit:disabled { cursor: wait; opacity: 0.72; }

        .right-panel {
            display: grid;
            align-content: start;
            gap: 22px;
            height: 100vh;
            padding: 24px;
            overflow: auto;
            background: var(--right-bg);
        }

        .panel {
            border: 1px solid var(--border);
            border-radius: 18px;
            background: var(--surface);
            box-shadow: var(--shadow-soft);
            padding: 28px;
        }

        .panel-head {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            margin-bottom: 22px;
        }

        .panel-icon {
            width: 32px;
            height: 32px;
            flex: 0 0 auto;
            border-radius: 11px;
            background: rgba(37, 99, 235, 0.10);
            box-shadow: none;
            color: var(--brand-blue);
            font-size: 15px;
        }

        .panel h3 {
            margin: 0 0 4px;
            font-size: 15px;
            letter-spacing: -0.02em;
        }

        .panel p {
            margin: 0;
            color: var(--text-secondary);
            font-size: 13px;
            line-height: 1.55;
        }

        .question-list {
            display: grid;
            gap: 14px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .question-list button {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            width: 100%;
            border: 0;
            border-radius: 12px;
            background: transparent;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 12px 10px;
            font-size: 14px;
            font-weight: 750;
            line-height: 1.45;
            text-align: left;
        }

        .question-list button::before {
            flex: 0 0 auto;
            color: var(--text-secondary);
            content: "⚡";
            font-size: 12px;
            line-height: 1.6;
        }

        .question-list button:hover { background: rgba(37, 99, 235, 0.07); color: var(--text-primary); }

        .system-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 14px;
            padding: 12px 0;
            color: var(--text-secondary);
            font-size: 13px;
        }

        .pill {
            border-radius: 999px;
            background: var(--blue-soft);
            color: var(--brand-blue);
            padding: 3px 8px;
            font-weight: 850;
        }

        .tip-panel p {
            font-size: 13px;
            line-height: 1.7;
        }

        @media (max-width: 1180px) {
            .app-shell { grid-template-columns: 232px minmax(0, 1fr); }
            .right-panel { display: none; }
            .main { border-right: 0; }
        }

        @media (max-width: 820px) {
            .app-shell { display: block; }
            .sidebar { position: static; height: auto; }
            .recent { display: none; }
            .sidebar-footer { display: none; }
            .nav { grid-template-columns: repeat(3, 1fr); }
            .brand { padding: 20px; }
            .topbar { padding: 18px 20px; align-items: flex-start; flex-direction: column; }
            .conversation { min-height: 44vh; padding: 24px 18px; }
            .message-bubble,
            .message-sources { max-width: 100%; width: calc(100% - 46px); }
            .composer-wrap { padding: 18px; }
            textarea { padding-right: 18px; padding-bottom: 70px; }
            .composer-meta { left: 14px; justify-content: space-between; }
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="brand">
                <img class="brand-logo" src="{{ asset('images/Logo-drsp.png') }}" alt="DRSP Assistente Virtual">
            </div>

            <nav class="nav" aria-label="Navegação principal">
                <button type="button" class="active" id="new-chat-button"><span><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg></span> Nova Conversa</button>
            </nav>

            <div class="sidebar-footer nav">
                <button type="button" id="settings-button"><span><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.38a2 2 0 0 0-.73-2.73l-.15-.09a2 2 0 0 1-1-1.74v-.51a2 2 0 0 1 1-1.72l.15-.1a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg></span> Configurações</button>
            </div>
        </aside>

        <main class="main">
            <header class="topbar">
                <div class="topbar-title">
                    <h2 id="main-title">Chat com IA</h2>
                    <div id="ollama-status" class="status checking" data-health-url="/chat-drsp/index.php/chat/health">
                        <span class="dot" aria-hidden="true"></span>
                        <span id="ollama-status-text">Verificando Ollama...</span>
                    </div>
                </div>
                <button type="button" class="ghost-button" id="clear-chat-button"><svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/><path d="M3 21v-5h5"/></svg> Limpar</button>
                <button type="button" class="ghost-button" id="back-chat-button" hidden>Voltar ao Chat</button>
            </header>

            <section class="conversation" aria-live="polite">
                <div class="response-stack">
                    <div class="empty-state" id="empty-state">
                        <div class="hero-mark"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4 20-7z"/></svg></div>
                        <h3>Bem-vindo ao Chat DRSP</h3>
                        <p>Faça suas perguntas sobre procedimentos DRSP/SUAS e obtenha respostas da base de conhecimento local.</p>
                    </div>

                    <article id="stream-answer" class="message-row assistant" hidden>
                        <span class="message-avatar"><svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/><path d="M2 14h2"/><path d="M20 14h2"/><path d="M15 13v2"/><path d="M9 13v2"/></svg></span>
                        <div class="message-bubble">
                            <span class="message-label">Assistente</span>
                            <div id="answer-output"></div>
                        </div>
                    </article>

                    <details id="stream-sources" class="alert answer sources-disclosure message-sources" hidden>
                        <summary class="sources-summary">Fontes consultadas</summary>
                        <ul id="source-list" class="source-list"></ul>
                    </details>

                    @if (session('answer'))
                        <article class="alert answer">
                            <div class="answer-title">Resposta</div>
                            <div>{{ session('answer') }}</div>
                        </article>
                    @endif

                    @if (session('sources'))
                        <details class="alert answer sources-disclosure">
                            <summary class="sources-summary">Fontes consultadas</summary>
                            <ul class="source-list">
                                @foreach (session('sources') as $source)
                                    <li class="source-item">
                                        <span class="source-name">{{ $source['excerpt_number'] }}. {{ $source['title'] }}@if ($source['original_name']) — {{ $source['original_name'] }}@endif</span>
                                        <div class="source-excerpt">{{ $source['excerpt'] }}</div>
                                        @if ($source['chunk_index'] !== null || $source['extension'])
                                            <div class="source-meta">
                                                @if ($source['chunk_index'] !== null) Chunk {{ $source['chunk_index'] + 1 }} @endif
                                                @if ($source['extension']) · {{ strtoupper($source['extension']) }} @endif
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </details>
                    @endif

                    @if (session('error'))
                        <div class="alert error">{{ session('error') }}</div>
                    @endif

                    <div id="stream-error" class="alert error" hidden></div>
                </div>
            </section>

            <section class="composer-wrap">
                <form id="chat-form" method="POST" action="/chat-drsp/index.php/chat" data-stream-url="/chat-drsp/index.php/chat/stream" class="composer">
                    @csrf
                    <textarea id="message" name="message" maxlength="4000" placeholder="Digite sua pergunta sobre DRSP/SUAS..." required>{{ old('message') }}</textarea>
                    @error('message')
                        <div class="alert error">{{ $message }}</div>
                    @enderror
                    <div class="composer-meta">
                        <span class="counter"><span id="char-count">0</span>/4000</span>
                        <button id="submit-button" type="submit" class="submit"><svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4 20-7z"/></svg> Enviar</button>
                    </div>
                </form>
            </section>

            <section id="settings-screen" class="conversation is-settings" hidden>
                <div class="settings-screen">
                    @if (session('documents_admin_authenticated') === true)
                        <article class="settings-card session-card">
                            <div>
                                <div class="settings-head">
                                    <span class="settings-icon"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><rect width="14" height="10" x="5" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
                                    <div>
                                        <h3>Sessão Ativa</h3>
                                        <p>Você está autenticado como administrador</p>
                                    </div>
                                </div>
                                <form method="POST" action="/chat-drsp/index.php/documents/logout">
                                    @csrf
                                    <button type="submit" class="logout-mini"><svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg> Sair</button>
                                </form>
                            </div>
                            <span class="connected-badge">Conectado</span>
                        </article>
                    @endif

                    <article class="settings-card">
                        <div class="settings-head">
                            <span class="settings-icon"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg></span>
                            <div>
                                <h3>Aparência</h3>
                                <p>Personalize a aparência da interface</p>
                            </div>
                        </div>
                        <div class="settings-row">
                            <div>
                                <span class="settings-label">Modo Escuro</span>
                                <p>Ative o tema escuro para reduzir o brilho da tela</p>
                            </div>
                            <button type="button" id="dark-mode-toggle" class="switch" aria-label="Modo escuro" aria-pressed="false"></button>
                        </div>
                    </article>

                    <article class="settings-card">
                        <div class="settings-head">
                            <span class="settings-icon"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg></span>
                            <div>
                                <h3>Base Interna</h3>
                                <p>Envie documentos do DRSP/SUAS para o chat consultar antes de responder.</p>
                                @if (session('documents_admin_authenticated') !== true)
                                    <p class="settings-login-note"><svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><rect width="14" height="10" x="5" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> Login necessário para acessar</p>
                                @endif
                            </div>
                        </div>
                        <a class="settings-action" href="{{ session('documents_admin_authenticated') === true ? '/chat-drsp/index.php/documents' : '/chat-drsp/index.php/documents/login' }}">
                            @if (session('documents_admin_authenticated') === true)
                                <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg> Gerenciar documentos
                            @else
                                <svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/></svg> Fazer login e gerenciar
                            @endif
                        </a>
                    </article>

                    <article class="settings-card">
                        <div class="settings-head">
                            <span class="settings-icon"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><rect width="16" height="20" x="4" y="2" rx="2"/><path d="M8 6h8"/><path d="M8 10h8"/><path d="M8 14h5"/></svg></span>
                            <div>
                                <h3>Modelo de IA</h3>
                                <p>Configurações do modelo Ollama</p>
                            </div>
                        </div>
                        <div class="settings-row">
                            <div>
                                <span class="settings-label">Modelo Atual</span>
                                <span class="settings-badge">Ollama Local</span>
                            </div>
                        </div>
                        <div class="settings-row">
                            <div>
                                <span class="settings-label">Temperatura</span>
                                <span class="settings-badge settings-badge-muted">0.7</span>
                            </div>
                        </div>
                    </article>
                </div>
            </section>
        </main>

        <aside class="right-panel">
            <section class="panel">
                <div class="panel-head">
                    <div class="panel-icon"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M9.09 9a3 3 0 1 1 5.82 1c0 2-3 2-3 4"/><path d="M12 17h.01"/></svg></div>
                    <div>
                        <h3>Exemplos de Perguntas</h3>
                        <p>Clique para usar</p>
                    </div>
                </div>
                <ul class="question-list">
                    <li><button type="button" data-prompt="Como funciona o cadastro no SUAS?">Como funciona o cadastro no SUAS?</button></li>
                    <li><button type="button" data-prompt="Procedimentos para atualização cadastral">Procedimentos para atualização cadastral</button></li>
                    <li><button type="button" data-prompt="Documentação necessária para benefícios">Documentação necessária para benefícios</button></li>
                </ul>
                
            </section>

            <section class="panel">
                <div class="panel-head">
                    <div class="panel-icon"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg></div>
                    <div><h3>Informações do Sistema</h3></div>
                </div>
                <div class="system-row"><span>Modelo IA</span><span class="pill">Ollama</span></div>
                <div class="system-row"><span>Base</span><span class="pill">DRSP</span></div>
                <div class="system-row"><span>Status</span><span class="pill">Local</span></div>
            </section>

            <section class="panel tip-panel">
                <div class="panel-head">
                    <div class="panel-icon"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 14c.2-1 .7-1.7 1.5-2.5A4.2 4.2 0 0 0 12 4a4.2 4.2 0 0 0-4.5 7.5c.8.8 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/></svg></div>
                    <div><h3>Dica</h3></div>
                </div>
                <p>Para melhores resultados, seja específico em suas perguntas e inclua contexto relevante sobre o caso.</p>
            </section>
        </aside>
    </div>

    <script>
        const form = document.getElementById('chat-form');
        const textarea = document.getElementById('message');
        const submitButton = document.getElementById('submit-button');
        const streamAnswer = document.getElementById('stream-answer');
        const answerOutput = document.getElementById('answer-output');
        const streamSources = document.getElementById('stream-sources');
        const sourceList = document.getElementById('source-list');
        const streamError = document.getElementById('stream-error');
        const statusBadge = document.getElementById('ollama-status');
        const statusText = document.getElementById('ollama-status-text');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const defaultButtonText = submitButton.textContent;
        const chatHistory = [];
        const charCount = document.getElementById('char-count');
        const emptyState = document.getElementById('empty-state');
        const clearChatButton = document.getElementById('clear-chat-button');
        const backChatButton = document.getElementById('back-chat-button');
        const newChatButton = document.getElementById('new-chat-button');
        const settingsButton = document.getElementById('settings-button');
        const settingsScreen = document.getElementById('settings-screen');
        const conversation = document.querySelector('.main > .conversation');
        const composerWrap = document.querySelector('.composer-wrap');
        const mainTitle = document.getElementById('main-title');

        const darkModeToggle = document.getElementById('dark-mode-toggle');

        function applyTheme(theme) {
            const isDark = theme === 'dark';
            document.documentElement.dataset.theme = theme;
            darkModeToggle.classList.toggle('is-on', isDark);
            darkModeToggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        }

        applyTheme(localStorage.getItem('chat-drsp-theme') || 'light');

        darkModeToggle.addEventListener('click', () => {
            const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
            localStorage.setItem('chat-drsp-theme', nextTheme);
            applyTheme(nextTheme);
        });

        function showChat() {
            settingsScreen.hidden = true;
            conversation.hidden = false;
            composerWrap.hidden = false;
            clearChatButton.hidden = false;
            backChatButton.hidden = true;
            mainTitle.textContent = 'Chat com IA';
            newChatButton.classList.add('active');
            settingsButton.classList.remove('active');
        }

        function showSettings() {
            conversation.hidden = true;
            composerWrap.hidden = true;
            settingsScreen.hidden = false;
            clearChatButton.hidden = true;
            backChatButton.hidden = false;
            mainTitle.textContent = 'Configurações';
            newChatButton.classList.remove('active');
            settingsButton.classList.add('active');
        }

        function updateCharacterCount() {
            charCount.textContent = textarea.value.length;
        }

        function appendMessage(role, content = '') {
            const row = document.createElement('article');
            row.className = `message-row ${role}`;

            const avatar = document.createElement('span');
            avatar.className = 'message-avatar';
            avatar.innerHTML = role === 'user'
                ? '<svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>'
                : '<svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/><path d="M2 14h2"/><path d="M20 14h2"/><path d="M15 13v2"/><path d="M9 13v2"/></svg>';

            const bubble = document.createElement('div');
            bubble.className = 'message-bubble';

            const label = document.createElement('span');
            label.className = 'message-label';
            label.textContent = role === 'user' ? 'Você' : 'Assistente';

            const body = document.createElement('div');
            body.className = 'message-content';
            body.textContent = content;

            bubble.append(label, body);
            row.append(avatar, bubble);
            streamAnswer.before(row);

            return { row, body };
        }

        function attachSourcesToMessage(encodedSources) {
            renderSources(encodedSources);

            if (!streamSources.hidden) {
                streamAnswer.before(streamSources);
            }
        }

        function scrollConversationToBottom() {
            conversation.scrollTo({ top: conversation.scrollHeight, behavior: 'smooth' });
        }

        function historyForRequest() {
            return chatHistory.slice(-8).map((item) => ({
                role: item.role,
                content: item.content.slice(0, 1600),
            }));
        }

        function addHistory(role, content) {
            chatHistory.push({ role, content: content.trim() });

            if (chatHistory.length > 12) {
                chatHistory.splice(0, chatHistory.length - 12);
            }
        }

        function clearConversation() {
            textarea.value = '';
            chatHistory.length = 0;
            document.querySelectorAll('.response-stack > .message-row').forEach((message) => message.remove());
            answerOutput.textContent = '';
            streamAnswer.hidden = true;
            renderSources(null);
            streamError.hidden = true;
            streamError.textContent = '';
            emptyState.hidden = false;
            updateCharacterCount();
            textarea.focus();
        }

        async function checkOllamaStatus() {
            statusBadge.classList.remove('ok', 'error');
            statusBadge.classList.add('checking');
            statusText.textContent = 'Verificando Ollama...';

            try {
                const response = await fetch(statusBadge.dataset.healthUrl, {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await response.json();

                statusBadge.classList.remove('checking');
                statusBadge.classList.toggle('ok', response.ok && data.ok === true);
                statusBadge.classList.toggle('error', !response.ok || data.ok !== true);
                statusText.textContent = data.message || (response.ok ? 'Ollama conectado' : 'Ollama indisponível');
            } catch (error) {
                statusBadge.classList.remove('checking', 'ok');
                statusBadge.classList.add('error');
                statusText.textContent = 'Ollama indisponível';
            }
        }

        checkOllamaStatus();
        setInterval(checkOllamaStatus, 30000);
        updateCharacterCount();

        textarea.addEventListener('input', updateCharacterCount);
        clearChatButton.addEventListener('click', clearConversation);
        newChatButton.addEventListener('click', () => {
            showChat();
            clearConversation();
        });
        settingsButton.addEventListener('click', showSettings);
        backChatButton.addEventListener('click', showChat);

        document.querySelectorAll('[data-prompt]').forEach((button) => {
            button.addEventListener('click', () => {
                textarea.value = button.dataset.prompt;
                updateCharacterCount();
                textarea.focus();
            });
        });

        function renderSources(encodedSources) {
            sourceList.innerHTML = '';
            streamSources.hidden = true;
            streamSources.open = false;

            if (!encodedSources) {
                return;
            }

            let sources = [];

            try {
                sources = JSON.parse(decodeURIComponent(escape(atob(encodedSources))));
            } catch (error) {
                sources = [];
            }

            if (!Array.isArray(sources) || sources.length === 0) {
                return;
            }

            sources.forEach((source) => {
                const item = document.createElement('li');
                item.className = 'source-item';

                const name = document.createElement('span');
                name.className = 'source-name';
                name.textContent = `${source.excerpt_number}. ${source.title}${source.original_name ? ' — ' + source.original_name : ''}`;

                const excerpt = document.createElement('div');
                excerpt.className = 'source-excerpt';
                excerpt.textContent = source.excerpt || '';

                item.append(name, excerpt);

                const metaParts = [];

                if (source.chunk_index !== null && source.chunk_index !== undefined) {
                    metaParts.push(`Chunk ${Number(source.chunk_index) + 1}`);
                }

                if (source.extension) {
                    metaParts.push(String(source.extension).toUpperCase());
                }

                if (metaParts.length > 0) {
                    const meta = document.createElement('div');
                    meta.className = 'source-meta';
                    meta.textContent = metaParts.join(' · ');
                    item.append(meta);
                }

                sourceList.append(item);
            });

            streamSources.hidden = false;
        }

        form.addEventListener('submit', async (event) => {
            if (!window.ReadableStream) {
                return;
            }

            event.preventDefault();

            const message = textarea.value.trim();

            if (!message) {
                streamError.textContent = 'Digite uma mensagem para enviar.';
                streamError.hidden = false;
                return;
            }

            emptyState.hidden = true;
            streamError.hidden = true;
            streamError.textContent = '';
            streamAnswer.hidden = true;
            answerOutput.textContent = '';
            renderSources(null);

            const history = historyForRequest();

            appendMessage('user', message);
            addHistory('user', message);
            const assistantMessage = appendMessage('assistant');
            textarea.value = '';
            updateCharacterCount();
            scrollConversationToBottom();

            submitButton.disabled = true;
            submitButton.textContent = 'Gerando...';

            try {
                const response = await fetch(form.dataset.streamUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'text/plain',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ message, history }),
                });

                if (!response.ok || !response.body) {
                    const errorMessage = response.status === 419
                        ? 'Sessão expirada. Recarregue a página e tente novamente.'
                        : 'Não foi possível iniciar a resposta em tempo real.';

                    throw new Error(errorMessage);
                }

                const reader = response.body.getReader();
                const decoder = new TextDecoder();

                while (true) {
                    const { value, done } = await reader.read();

                    if (done) {
                        break;
                    }

                    assistantMessage.body.textContent += decoder.decode(value, { stream: true });
                    scrollConversationToBottom();
                }

                addHistory('assistant', assistantMessage.body.textContent);
                attachSourcesToMessage(response.headers.get('X-Knowledge-Sources'));
            } catch (error) {
                assistantMessage.row.remove();
                streamError.textContent = error.message || 'Não foi possível conectar ao Ollama. Inicie o Ollama e tente novamente.';
                streamError.hidden = false;
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = defaultButtonText;
                textarea.focus();
            }
        });
    </script>
</body>
</html>
