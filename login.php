<?php
declare(strict_types=1);
require_once __DIR__ . '/config/bootstrap.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Dynamic Content Loader</title>
    <style>
        :root {
            --bg-color: #f7fafc;
            --text-color: #1a202c;
            --text-heading: #2d3748;
            --text-muted: #718096;
            --primary-accent: #667eea;
            --secondary-accent: #764ba2;
            --primary-gradient: linear-gradient(135deg, var(--primary-accent) 0%, var(--secondary-accent) 100%);
            --card-bg: rgba(255, 255, 255, 0.82);
            --card-border: rgba(226, 232, 240, 0.82);
            --card-shadow: 0 4px 15px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            --card-shadow-hover: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --input-bg: #fff;
            --input-border: #e2e8f0;
            --input-focus: rgba(102, 126, 234, 0.35);
            --glass-blur: blur(12px);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body { min-height: 100%; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: var(--text-color);
            background:
                radial-gradient(circle at 15% 20%, rgba(102, 126, 234, 0.10) 0%, transparent 24%),
                radial-gradient(circle at 88% 12%, rgba(118, 75, 162, 0.09) 0%, transparent 22%),
                radial-gradient(circle at 76% 86%, rgba(102, 126, 234, 0.08) 0%, transparent 20%),
                linear-gradient(180deg, #ffffff 0%, var(--bg-color) 100%);
        }

        .auth-page {
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(420px, 0.95fr);
        }

        .auth-hero {
            position: relative;
            overflow: hidden;
            padding: 44px 48px 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-hero::before,
        .auth-hero::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            filter: blur(2px);
            opacity: 0.85;
        }

        .auth-hero::before {
            inset: auto auto 8% -6%;
            width: 340px;
            height: 340px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.18), rgba(255, 255, 255, 0));
        }

        .auth-hero::after {
            inset: 14% 12% auto auto;
            width: 220px;
            height: 220px;
            background: linear-gradient(135deg, rgba(118, 75, 162, 0.12), rgba(255, 255, 255, 0));
        }

        .brand-mark {
            position: absolute;
            top: 36px;
            left: 40px;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: var(--primary-gradient);
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 2rem;
            font-weight: 800;
            box-shadow: 0 14px 28px rgba(90, 95, 207, 0.28);
        }

        .hero-copy {
            position: relative;
            z-index: 1;
            max-width: 620px;
            width: 100%;
            display: grid;
            gap: 28px;
        }

        .hero-title {
            font-size: clamp(2.9rem, 5vw, 5.25rem);
            line-height: 0.96;
            letter-spacing: -0.06em;
            font-weight: 800;
            margin: 0;
            color: var(--text-color);
        }

        .hero-title .accent {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
        }

        .hero-visual {
            position: relative;
            height: min(68vh, 680px);
            min-height: 520px;
        }

        .card-stack {
            position: absolute;
            inset: 6% 12% 8% 10%;
        }

        .stack-card {
            position: absolute;
            border-radius: 28px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
        }

        .stack-card.card-1 {
            left: 5%;
            top: 22%;
            width: 44%;
            height: 44%;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.94) 0%, rgba(51, 65, 85, 0.94) 100%);
        }

        .stack-card.card-1::before,
        .stack-card.card-1::after {
            content: '';
            position: absolute;
            left: 10%;
            right: 10%;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.9);
        }

        .stack-card.card-1::before {
            top: 14%;
            height: 8px;
        }

        .stack-card.card-1::after {
            top: 36%;
            height: 56%;
            background:
                linear-gradient(180deg, rgba(59, 130, 246, 0.95), rgba(37, 99, 235, 0.78)),
                linear-gradient(180deg, rgba(255,255,255,0.18), rgba(255,255,255,0));
            clip-path: polygon(0 0, 100% 0, 84% 100%, 0 100%);
        }

        .stack-card.card-2 {
            right: 10%;
            top: 2%;
            width: 54%;
            height: 62%;
            background: linear-gradient(180deg, rgba(226, 232, 240, 0.95) 0%, rgba(186, 201, 231, 0.95) 100%);
        }

        .stack-card.card-2::before {
            content: '';
            position: absolute;
            inset: 10% 12% 18%;
            border-radius: 22px;
            background:
                radial-gradient(circle at 28% 28%, #ffffff 0 12px, transparent 13px),
                linear-gradient(135deg, #dbeafe 0%, #93c5fd 42%, #2563eb 100%);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.55);
        }

        .stack-card.card-2::after {
            content: '';
            position: absolute;
            left: 16%;
            right: 16%;
            bottom: 12%;
            height: 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow:
                0 -18px 0 rgba(255, 255, 255, 0.55),
                0 -36px 0 rgba(255, 255, 255, 0.4);
        }

        .stack-card.card-3 {
            left: 18%;
            bottom: 8%;
            width: 40%;
            height: 36%;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.96) 0%, rgba(240, 246, 255, 0.96) 100%);
        }

        .stack-card.card-3::before,
        .stack-card.card-3::after {
            content: '';
            position: absolute;
            left: 12%;
            right: 12%;
            border-radius: 999px;
            background: #e5eefc;
        }

        .stack-card.card-3::before {
            top: 62%;
            height: 10px;
            box-shadow: 0 -18px 0 #e5eefc, 0 -36px 0 #e5eefc;
        }

        .stack-card.card-3::after {
            top: 16%;
            height: 42%;
            background: linear-gradient(180deg, rgba(24, 119, 242, 0.16), rgba(24, 119, 242, 0.02));
            border-radius: 18px;
        }

        .hero-avatar {
            position: absolute;
            right: 12%;
            bottom: 4%;
            width: 34%;
            aspect-ratio: 1;
            border-radius: 50%;
            background: linear-gradient(135deg, #ffe08a 0%, #f59e0b 100%);
            border: 6px solid #fff;
            box-shadow: 0 18px 32px rgba(16, 24, 40, 0.18);
        }

        .hero-avatar::before,
        .hero-avatar::after {
            content: '';
            position: absolute;
            inset: 14% 18%;
            border-radius: 40% 40% 42% 42%;
            background: linear-gradient(180deg, #7c3f1a 0%, #3f1d0d 100%);
            clip-path: polygon(20% 0, 80% 0, 100% 32%, 92% 100%, 8% 100%, 0 32%);
            opacity: 0.9;
        }

        .hero-avatar::after {
            inset: auto 24% 18% 24%;
            height: 32%;
            background: linear-gradient(180deg, #38bdf8 0%, #2563eb 100%);
            clip-path: ellipse(50% 48% at 50% 50%);
            opacity: 0.85;
        }

        .emoji-bubble,
        .badge,
        .heart-bubble {
            position: absolute;
            display: grid;
            place-items: center;
            border-radius: 50%;
            font-weight: 700;
            box-shadow: 0 12px 28px rgba(16, 24, 40, 0.15);
        }

        .emoji-bubble {
            left: 8%;
            top: 10%;
            width: 70px;
            height: 70px;
            background: linear-gradient(180deg, #e0e7ff 0%, #93c5fd 100%);
            color: #1d4ed8;
            font-size: 1.5rem;
        }

        .badge {
            right: 8%;
            top: 18%;
            width: 92px;
            height: 38px;
            border-radius: 999px;
            background: var(--secondary-accent);
            color: #fff;
            font-size: 0.95rem;
        }

        .heart-bubble {
            right: 14%;
            bottom: 20%;
            width: 78px;
            height: 78px;
            background: linear-gradient(180deg, #fb7185 0%, #e11d48 100%);
            color: #fff;
            font-size: 2rem;
        }

        .auth-panel {
            display: grid;
            place-items: center;
            padding: 28px 30px;
            border-left: 1px solid rgba(16, 24, 40, 0.08);
            background: linear-gradient(180deg, rgba(255,255,255,0.92), rgba(247,250,252,0.94));
        }

        .auth-card {
            width: min(100%, 480px);
            padding: 26px 24px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 28px;
            box-shadow: var(--card-shadow);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
        }

        .auth-card h1 {
            margin: 0 0 12px;
            font-size: 2.1rem;
            letter-spacing: -0.04em;
            color: var(--text-heading);
        }

        .auth-card p.lead {
            margin: 0 0 22px;
            color: var(--text-muted);
            font-size: 1rem;
            line-height: 1.6;
        }

        .notice {
            display: none;
            margin: 0 0 16px;
            border-radius: 14px;
            padding: 12px 14px;
            font-size: 0.95rem;
            border: 1px solid transparent;
        }

        .notice.error {
            display: block;
            background: #fff1f2;
            color: #9f1239;
            border-color: #fecdd3;
        }

        .notice.success {
            display: block;
            background: #ecfdf5;
            color: #065f46;
            border-color: #bbf7d0;
        }

        .field { margin-bottom: 14px; }

        .field input {
            width: 100%;
            height: 58px;
            padding: 0 18px;
            border: 2px solid var(--input-border);
            border-radius: 14px;
            background: var(--input-bg);
            font-size: 1rem;
            color: var(--text-color);
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        .field input::placeholder { color: #98a2b3; }

        .field input:focus {
            border-color: var(--primary-accent);
            box-shadow: 0 0 0 3px var(--input-focus);
        }

        .btn {
            width: 100%;
            height: 54px;
            border: 0;
            border-radius: 14px;
            background: var(--primary-gradient);
            color: #fff;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 6px;
            box-shadow: 0 16px 30px -16px rgba(90, 95, 207, 0.72);
            transition: transform 0.2s ease, background 0.2s ease;
        }

        .btn:hover { transform: translateY(-1px); }

        .forgot-link,
        .meta-link {
            display: block;
            text-align: center;
            color: var(--primary-accent);
            text-decoration: none;
            font-weight: 600;
        }

        .forgot-link {
            margin: 18px 0 28px;
            color: var(--text-heading);
            font-weight: 500;
        }

        .divider-space { height: 22px; }

        .create-btn {
            display: block;
            width: 100%;
            height: 54px;
            line-height: 52px;
            text-align: center;
            border-radius: 14px;
            border: 1px solid rgba(102, 126, 234, 0.4);
            color: var(--primary-accent);
            text-decoration: none;
            font-size: 1rem;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.95);
        }

        .meta-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 18px;
            color: var(--text-muted);
            font-weight: 600;
        }

        .meta-brand span {
            display: inline-grid;
            place-items: center;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 2px solid var(--primary-accent);
            color: var(--primary-accent);
            font-size: 0.8rem;
        }

        @media (max-width: 1024px) {
            .auth-page { grid-template-columns: 1fr; }
            .auth-hero { min-height: 46vh; }
            .auth-panel { border-left: 0; border-top: 1px solid rgba(16, 24, 40, 0.08); }
        }

        @media (max-width: 640px) {
            .auth-hero { padding: 28px 18px 20px; }
            .brand-mark { top: 18px; left: 18px; }
            .hero-copy { gap: 18px; }
            .hero-title { font-size: clamp(2.2rem, 12vw, 3.8rem); }
            .hero-visual { min-height: 360px; height: 48vh; }
            .auth-panel { padding: 18px; }
            .auth-card { padding: 22px 18px; }
        }
    </style>
</head>
<body>
    <main class="auth-page">
        <section class="auth-hero" aria-label="Welcome panel">
            <div class="brand-mark">D</div>
            <div class="hero-copy">
                <h2 class="hero-title">Load the content <span class="accent">you need</span>.</h2>
                <div class="hero-visual" aria-hidden="true">
                    <div class="card-stack">
                        <div class="stack-card card-1"></div>
                        <div class="stack-card card-2"></div>
                        <div class="stack-card card-3"></div>
                        <div class="emoji-bubble">&lt;/&gt;</div>
                        <div class="badge">API</div>
                        <div class="heart-bubble">⚡</div>
                        <div class="hero-avatar"></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="auth-panel" aria-label="Login form">
            <div class="auth-card">
                <h1>Log in to Dynamic Content Loader</h1>
                <p class="lead">Sign in to fetch the latest posts, filter content, and manage your feed.</p>
                <div id="notice" class="notice"></div>
                <form id="loginForm" novalidate>
                    <div class="field">
                        <input id="email" type="email" required autocomplete="email" placeholder="Email address">
                    </div>
                    <div class="field">
                        <input id="password" type="password" required autocomplete="current-password" placeholder="Password">
                    </div>
                    <button class="btn" type="submit">Log in</button>
                </form>
                <a class="forgot-link" href="#">Forgot password?</a>
                <a class="create-btn" href="register.php">Create new account</a>
                <div class="meta-brand"><span>D</span> Dynamic Content Loader</div>
            </div>
        </section>
    </main>

    <script>
        const form = document.getElementById('loginForm');
        const notice = document.getElementById('notice');

        function showNotice(message, type) {
            notice.className = 'notice ' + type;
            notice.textContent = message;
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const payload = {
                email: document.getElementById('email').value.trim(),
                password: document.getElementById('password').value
            };

            if (!payload.email || !payload.password) {
                showNotice('Please fill in all fields.', 'error');
                return;
            }

            try {
                const response = await fetch('auth/login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                if (!response.ok) {
                    showNotice(data.error || 'Login failed.', 'error');
                    return;
                }

                showNotice('Login successful. Redirecting...', 'success');
                setTimeout(() => { window.location.href = 'index.php'; }, 450);
            } catch (error) {
                showNotice('Network error. Please try again.', 'error');
            }
        });
    </script>
</body>
</html>
