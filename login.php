<?php
// ShopBR - Auth Page (Login + Register)
require_once __DIR__ . '/config/session.php';
iniciarSessao();

// Se já está logado, redireciona direto
if (estaLogado()) {
    $destino = eVendedor() ? 'dashboard-vendedor.php' : 'index.php';
    header('Location: ' . $destino);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopBR — Entrar / Criar conta</title>
    <meta name="description" content="Faça login ou crie sua conta no ShopBR.">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- ══ HEADER ══ -->
<header class="site-header" role="banner">
    <div class="container header-inner">
        <a href="index.php" class="site-logo" aria-label="ShopBR — Página inicial">Shop<span>BR</span></a>
        <nav class="header-nav" style="margin-left:auto;display:flex;" role="navigation" aria-label="Navegação">
            <a href="index.php" style="color:rgba(255,255,255,0.7);">← Voltar ao início</a>
        </nav>
    </div>
</header>

<main>
    <div class="auth-page">
        <div class="auth-card" role="main">

            <div class="auth-logo">
                <span class="logo-text">Shop<span>BR</span></span>
            </div>

            <!-- Tabs -->
            <div class="auth-tabs" role="tablist" aria-label="Painéis de autenticação">
                <button
                    class="auth-tab active"
                    id="tab-login"
                    role="tab"
                    aria-selected="true"
                    aria-controls="panel-login"
                    onclick="switchPanel('login')"
                >
                    Entrar
                </button>
                <button
                    class="auth-tab"
                    id="tab-register"
                    role="tab"
                    aria-selected="false"
                    aria-controls="panel-register"
                    onclick="switchPanel('register')"
                >
                    Criar Conta
                </button>
            </div>

            <!-- ── Login Panel ── -->
            <div class="auth-panel active" id="panel-login" role="tabpanel" aria-labelledby="tab-login">
                <h2>Bem-vindo de volta!</h2>
                <p>Acesse sua conta para continuar.</p>

                <div class="form-success" id="login-success" role="alert" aria-live="polite" style="display:none;"></div>
                <div class="form-error-banner" id="login-error" role="alert" aria-live="polite" style="display:none;"></div>

                <form id="login-form" novalidate aria-label="Formulário de login">
                    <div class="form-group">
                        <label for="login-email">E-mail</label>
                        <input
                            type="email"
                            id="login-email"
                            name="email"
                            class="form-control"
                            placeholder="seu@email.com"
                            autocomplete="email"
                            aria-required="true"
                            aria-describedby="login-email-error"
                        >
                        <span class="form-error" id="login-email-error" role="alert"></span>
                    </div>

                    <div class="form-group">
                        <label for="login-password">Senha</label>
                        <input
                            type="password"
                            id="login-password"
                            name="password"
                            class="form-control"
                            placeholder="Sua senha"
                            autocomplete="current-password"
                            aria-required="true"
                            aria-describedby="login-password-error"
                        >
                        <span class="form-error" id="login-password-error" role="alert"></span>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" id="login-btn">
                        Entrar
                    </button>
                </form>

                <div class="auth-toggle">
                    Não tem conta?
                    <a onclick="switchPanel('register')" role="button" tabindex="0" style="cursor:pointer;">
                        Criar conta
                    </a>
                </div>
            </div>

            <!-- ── Register Panel ── -->
            <div class="auth-panel" id="panel-register" role="tabpanel" aria-labelledby="tab-register">
                <h2>Crie sua conta</h2>
                <p>Preencha os dados abaixo para começar.</p>

                <div class="form-success" id="register-success" role="alert" aria-live="polite" style="display:none;"></div>
                <div class="form-error-banner" id="register-error" role="alert" aria-live="polite" style="display:none;"></div>

                <form id="register-form" novalidate aria-label="Formulário de cadastro">
                    <div class="form-group">
                        <label for="reg-name">Nome completo</label>
                        <input
                            type="text"
                            id="reg-name"
                            name="name"
                            class="form-control"
                            placeholder="Seu nome completo"
                            autocomplete="name"
                            maxlength="100"
                            aria-required="true"
                            aria-describedby="reg-name-error"
                        >
                        <span class="form-error" id="reg-name-error" role="alert"></span>
                    </div>

                    <div class="form-group">
                        <label for="reg-email">E-mail</label>
                        <input
                            type="email"
                            id="reg-email"
                            name="email"
                            class="form-control"
                            placeholder="seu@email.com"
                            autocomplete="email"
                            aria-required="true"
                            aria-describedby="reg-email-error"
                        >
                        <span class="form-error" id="reg-email-error" role="alert"></span>
                    </div>

                    <div class="form-group">
                        <label for="reg-password">Senha</label>
                        <input
                            type="password"
                            id="reg-password"
                            name="password"
                            class="form-control"
                            placeholder="Mínimo 8 caracteres"
                            autocomplete="new-password"
                            aria-required="true"
                            aria-describedby="reg-password-error"
                        >
                        <span class="form-error" id="reg-password-error" role="alert"></span>
                    </div>

                    <div class="form-group">
                        <label for="reg-confirm">Confirmar senha</label>
                        <input
                            type="password"
                            id="reg-confirm"
                            name="confirm_password"
                            class="form-control"
                            placeholder="Repita sua senha"
                            autocomplete="new-password"
                            aria-required="true"
                            aria-describedby="reg-confirm-error"
                        >
                        <span class="form-error" id="reg-confirm-error" role="alert"></span>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" id="register-btn">
                        Criar conta
                    </button>
                </form>

                <div class="auth-toggle">
                    Já tem conta?
                    <a onclick="switchPanel('login')" role="button" tabindex="0" style="cursor:pointer;">
                        Entrar
                    </a>
                </div>
            </div>

        </div>
    </div>
</main>

<footer class="site-footer" role="contentinfo" style="padding:var(--spacing-lg) 0;">
    <div class="container">
        <div class="footer-bottom" style="border:none;padding:0;">
            <p>&copy; <?= date('Y') ?> ShopBR. Todos os direitos reservados.</p>
        </div>
    </div>
</footer>

<script>
// ── Tab switching ──────────────────────────────────────────
function switchPanel(panel) {
    document.querySelectorAll('.auth-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.auth-tab').forEach(t => {
        t.classList.remove('active');
        t.setAttribute('aria-selected', 'false');
    });
    document.getElementById('panel-' + panel).classList.add('active');
    const tab = document.getElementById('tab-' + panel);
    tab.classList.add('active');
    tab.setAttribute('aria-selected', 'true');
}

// ── Helpers ───────────────────────────────────────────────
function showBanner(id, msg, isError = true) {
    const el = document.getElementById(id);
    el.textContent = msg;
    el.style.display = 'block';
    el.style.color = isError ? 'var(--color-error, #dc3545)' : 'var(--color-success, #28a745)';
}

function hideBanner(id) {
    const el = document.getElementById(id);
    if (el) { el.style.display = 'none'; el.textContent = ''; }
}

function setFieldError(fieldId, msg) {
    const input = document.getElementById(fieldId);
    const span  = document.getElementById(fieldId + '-error') ||
                  input?.parentElement?.querySelector('.form-error');
    if (input) input.classList.toggle('error', !!msg);
    if (span)  span.textContent = msg || '';
}

function clearErrors(prefix, fields) {
    fields.forEach(f => setFieldError(prefix + f, ''));
}

function setLoading(btnId, loading) {
    const btn = document.getElementById(btnId);
    if (!btn) return;
    btn.disabled = loading;
    btn.textContent = loading ? 'Aguarde...' : btn.dataset.label;
}

// Guarda os labels originais dos botões
window.addEventListener('DOMContentLoaded', () => {
    ['login-btn', 'register-btn'].forEach(id => {
        const btn = document.getElementById(id);
        if (btn) btn.dataset.label = btn.textContent.trim();
    });
});

// ── LOGIN ─────────────────────────────────────────────────
document.getElementById('login-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    hideBanner('login-error');
    hideBanner('login-success');
    clearErrors('login-', ['email', 'password']);

    const email = document.getElementById('login-email').value.trim();
    const senha = document.getElementById('login-password').value;

    // Validação client-side básica
    let valid = true;
    if (!email) { setFieldError('login-email', 'Informe seu e-mail.'); valid = false; }
    if (!senha)  { setFieldError('login-password', 'Informe sua senha.'); valid = false; }
    if (!valid) return;

    setLoading('login-btn', true);

    try {
        const res  = await fetch('api/auth/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, senha }),
        });
        const data = await res.json();

        if (data.sucesso) {
            showBanner('login-success', data.mensagem, false);
            setTimeout(() => { window.location.href = data.redirect || 'index.php'; }, 800);
        } else {
            // Erros por campo
            if (data.erros) {
                if (data.erros.email)  setFieldError('login-email',    data.erros.email);
                if (data.erros.senha)  setFieldError('login-password', data.erros.senha);
            }
            showBanner('login-error', data.mensagem || 'Erro ao fazer login.');
        }
    } catch {
        showBanner('login-error', 'Erro de conexão. Verifique sua internet e tente novamente.');
    } finally {
        setLoading('login-btn', false);
    }
});

// ── REGISTER ──────────────────────────────────────────────
document.getElementById('register-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    hideBanner('register-error');
    hideBanner('register-success');
    clearErrors('reg-', ['name', 'email', 'password', 'confirm']);

    const nome            = document.getElementById('reg-name').value.trim();
    const email           = document.getElementById('reg-email').value.trim();
    const senha           = document.getElementById('reg-password').value;
    const confirmarSenha  = document.getElementById('reg-confirm').value;

    // Validação client-side
    let valid = true;
    if (!nome)  { setFieldError('reg-name',    'Informe seu nome.');      valid = false; }
    if (!email) { setFieldError('reg-email',   'Informe seu e-mail.');    valid = false; }
    if (!senha) { setFieldError('reg-password','Crie uma senha.');        valid = false; }
    else if (senha.length < 8) { setFieldError('reg-password', 'Mínimo 8 caracteres.'); valid = false; }
    if (senha !== confirmarSenha) { setFieldError('reg-confirm', 'As senhas não coincidem.'); valid = false; }
    if (!valid) return;

    setLoading('register-btn', true);

    try {
        const res  = await fetch('api/auth/register.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nome, email, senha, confirmar_senha: confirmarSenha }),
        });
        const data = await res.json();

        if (data.sucesso) {
            showBanner('register-success', data.mensagem, false);
            setTimeout(() => { window.location.href = 'index.php'; }, 1000);
        } else {
            if (data.erros) {
                if (data.erros.nome)            setFieldError('reg-name',    data.erros.nome);
                if (data.erros.email)           setFieldError('reg-email',   data.erros.email);
                if (data.erros.senha)           setFieldError('reg-password',data.erros.senha);
                if (data.erros.confirmar_senha) setFieldError('reg-confirm', data.erros.confirmar_senha);
            }
            showBanner('register-error', data.mensagem || 'Erro ao criar conta.');
        }
    } catch {
        showBanner('register-error', 'Erro de conexão. Tente novamente.');
    } finally {
        setLoading('register-btn', false);
    }
});
</script>
</body>
</html>
