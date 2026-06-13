<?php
// ShopBR - Auth Page (Login + Register)
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

                <div class="form-success" id="login-success" role="alert" aria-live="polite">
                    Funcionalidade disponível em breve.
                </div>

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

                    <button type="submit" class="btn btn-primary btn-block">
                        Entrar
                    </button>
                </form>

                <div class="auth-toggle">
                    Não tem conta?
                    <a onclick="switchPanel('register')" role="button" tabindex="0" aria-label="Mudar para o painel de criação de conta">
                        Criar conta
                    </a>
                </div>
            </div>

            <!-- ── Register Panel ── -->
            <div class="auth-panel" id="panel-register" role="tabpanel" aria-labelledby="tab-register">
                <h2>Crie sua conta</h2>
                <p>Preencha os dados abaixo para começar.</p>

                <div class="form-success" id="register-success" role="alert" aria-live="polite">
                    Funcionalidade disponível em breve.
                </div>

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
                            placeholder="Crie uma senha"
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

                    <button type="submit" class="btn btn-primary btn-block">
                        Criar conta
                    </button>
                </form>

                <div class="auth-toggle">
                    Já tem conta?
                    <a onclick="switchPanel('login')" role="button" tabindex="0" aria-label="Mudar para o painel de login">
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

<script src="js/login.js"></script>
</body>
</html>
