<?php
// ShopBR - Seller Registration Page
require_once __DIR__ . '/config/session.php';
iniciarSessao();

// Se já é vendedor, manda direto pro dashboard
if (eVendedor()) {
    header('Location: dashboard-vendedor.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopBR — Cadastro de Vendedor</title>
    <meta name="description" content="Cadastre-se como vendedor no ShopBR e comece a vender para milhões de clientes.">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- ══ HEADER ══ -->
<header class="site-header" role="banner">
    <div class="container header-inner">
        <a href="index.php" class="site-logo" aria-label="ShopBR — Página inicial">Shop<span>BR</span></a>
        <nav class="header-nav" style="margin-left:auto;display:flex;" role="navigation" aria-label="Navegação">
            <a href="index.php">← Voltar à Página Principal</a>
        </nav>
    </div>
</header>

<main>
    <section class="seller-page" aria-labelledby="seller-heading">
        <div class="container">

            <nav class="page-nav" aria-label="Caminho de navegação">
                <a href="index.php" class="back-link">
                    <span aria-hidden="true">←</span> Página Principal
                </a>
            </nav>

            <div class="seller-card" role="main">
                <div class="seller-card-header">
                    <h1 id="seller-heading">Cadastre-se como Vendedor</h1>
                    <p>Preencha os dados abaixo para criar sua conta de vendedor no ShopBR.</p>
                </div>

                <div class="form-success" id="seller-success" role="alert" aria-live="polite" style="display:none;"></div>
                <div class="form-error-banner" id="seller-error" role="alert" aria-live="polite" style="display:none;"></div>

                <form id="seller-form" novalidate aria-label="Formulário de cadastro de vendedor">

                    <div class="form-group">
                        <label for="seller-name">Nome completo <span aria-hidden="true" style="color:var(--color-error);">*</span></label>
                        <input
                            type="text"
                            id="seller-name"
                            name="name"
                            class="form-control"
                            placeholder="Seu nome completo"
                            maxlength="100"
                            autocomplete="name"
                            aria-required="true"
                            aria-describedby="seller-name-error"
                        >
                        <span class="form-error" id="seller-name-error" role="alert"></span>
                    </div>

                    <div class="form-group">
                        <label for="seller-email">E-mail <span aria-hidden="true" style="color:var(--color-error);">*</span></label>
                        <input
                            type="email"
                            id="seller-email"
                            name="email"
                            class="form-control"
                            placeholder="seu@email.com"
                            maxlength="254"
                            autocomplete="email"
                            aria-required="true"
                            aria-describedby="seller-email-error"
                        >
                        <span class="form-error" id="seller-email-error" role="alert"></span>
                    </div>

                    <div class="form-group">
                        <label for="seller-password">Senha <span aria-hidden="true" style="color:var(--color-error);">*</span></label>
                        <input
                            type="password"
                            id="seller-password"
                            name="password"
                            class="form-control"
                            placeholder="Mínimo 8 caracteres"
                            autocomplete="new-password"
                            aria-required="true"
                            aria-describedby="seller-password-error"
                        >
                        <span class="form-error" id="seller-password-error" role="alert"></span>
                    </div>

                    <div class="form-group">
                        <label for="seller-confirm">Confirmar senha <span aria-hidden="true" style="color:var(--color-error);">*</span></label>
                        <input
                            type="password"
                            id="seller-confirm"
                            name="confirm_password"
                            class="form-control"
                            placeholder="Repita sua senha"
                            autocomplete="new-password"
                            aria-required="true"
                            aria-describedby="seller-confirm-error"
                        >
                        <span class="form-error" id="seller-confirm-error" role="alert"></span>
                    </div>

                    <div class="form-group">
                        <label for="seller-store">Nome da loja <span aria-hidden="true" style="color:var(--color-error);">*</span></label>
                        <input
                            type="text"
                            id="seller-store"
                            name="store_name"
                            class="form-control"
                            placeholder="Nome da sua loja"
                            maxlength="100"
                            autocomplete="organization"
                            aria-required="true"
                            aria-describedby="seller-store-error"
                        >
                        <span class="form-error" id="seller-store-error" role="alert"></span>
                    </div>

                    <div class="form-group">
                        <label for="seller-cpf">CPF / CNPJ <span aria-hidden="true" style="color:var(--color-error);">*</span></label>
                        <input
                            type="text"
                            id="seller-cpf"
                            name="cpf_cnpj"
                            class="form-control"
                            placeholder="000.000.000-00 ou 00.000.000/0000-00"
                            maxlength="18"
                            autocomplete="off"
                            inputmode="numeric"
                            aria-required="true"
                            aria-describedby="seller-cpf-error seller-cpf-hint"
                        >
                        <small id="seller-cpf-hint" style="font-size:12px;color:var(--color-text-muted);">
                            Digite CPF (11 dígitos) ou CNPJ (14 dígitos). A formatação é automática.
                        </small>
                        <span class="form-error" id="seller-cpf-error" role="alert"></span>
                    </div>

                    <div class="form-group">
                        <label for="seller-phone">Telefone <span aria-hidden="true" style="color:var(--color-error);">*</span></label>
                        <input
                            type="tel"
                            id="seller-phone"
                            name="phone"
                            class="form-control"
                            placeholder="(00) 00000-0000"
                            maxlength="15"
                            autocomplete="tel"
                            inputmode="numeric"
                            aria-required="true"
                            aria-describedby="seller-phone-error seller-phone-hint"
                        >
                        <small id="seller-phone-hint" style="font-size:12px;color:var(--color-text-muted);">
                            Celular (11 dígitos) ou fixo (10 dígitos). A formatação é automática.
                        </small>
                        <span class="form-error" id="seller-phone-error" role="alert"></span>
                    </div>

                    <button type="submit" id="seller-btn" class="btn btn-primary btn-block" style="margin-top:8px;">
                        Cadastrar como Vendedor
                    </button>

                </form>

                <p style="font-size:12px;color:var(--color-text-muted);margin-top:16px;text-align:center;">
                    Ao se cadastrar, você concorda com nossos
                    <a href="#">Termos de Uso</a> e
                    <a href="#">Política de Privacidade</a>.
                </p>
            </div>

        </div>
    </section>
</main>

<footer class="site-footer" role="contentinfo" style="padding:var(--spacing-lg) 0;">
    <div class="container">
        <div class="footer-bottom" style="border:none;padding:0;">
            <p>&copy; <?= date('Y') ?> ShopBR. Todos os direitos reservados.</p>
        </div>
    </div>
</footer>

<script src="js/cadastro-vendedor.js"></script>
<script>
// ── Máscara CPF/CNPJ ──────────────────────────────────────
document.getElementById('seller-cpf').addEventListener('input', function () {
    let v = this.value.replace(/\D/g, '').slice(0, 14);
    if (v.length <= 11) {
        v = v.replace(/(\d{3})(\d)/, '$1.$2')
             .replace(/(\d{3})(\d)/, '$1.$2')
             .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    } else {
        v = v.replace(/(\d{2})(\d)/, '$1.$2')
             .replace(/(\d{3})(\d)/, '$1.$2')
             .replace(/(\d{3})(\d)/, '$1/$2')
             .replace(/(\d{4})(\d{1,2})$/, '$1-$2');
    }
    this.value = v;
});

// ── Máscara Telefone ──────────────────────────────────────
document.getElementById('seller-phone').addEventListener('input', function () {
    let v = this.value.replace(/\D/g, '').slice(0, 11);
    if (v.length <= 10) {
        v = v.replace(/(\d{2})(\d)/, '($1) $2')
             .replace(/(\d{4})(\d)/, '$1-$2');
    } else {
        v = v.replace(/(\d{2})(\d)/, '($1) $2')
             .replace(/(\d{5})(\d)/, '$1-$2');
    }
    this.value = v;
});

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

function setFieldError(id, msg) {
    const input = document.getElementById(id);
    const span  = document.getElementById(id + '-error');
    if (input) input.classList.toggle('error', !!msg);
    if (span)  span.textContent = msg || '';
}

function clearAll() {
    ['seller-name','seller-email','seller-password','seller-confirm',
     'seller-store','seller-cpf','seller-phone'].forEach(id => setFieldError(id, ''));
}

// ── Submit ────────────────────────────────────────────────
document.getElementById('seller-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    hideBanner('seller-error');
    hideBanner('seller-success');
    clearAll();

    const nome           = document.getElementById('seller-name').value.trim();
    const email          = document.getElementById('seller-email').value.trim();
    const senha          = document.getElementById('seller-password').value;
    const confirmarSenha = document.getElementById('seller-confirm').value;
    const nomeLoja       = document.getElementById('seller-store').value.trim();
    const cpfCnpj        = document.getElementById('seller-cpf').value.trim();
    const telefone       = document.getElementById('seller-phone').value.trim();

    // Validação client-side
    let valid = true;
    if (!nome)     { setFieldError('seller-name',     'Informe seu nome.');           valid = false; }
    if (!email)    { setFieldError('seller-email',    'Informe seu e-mail.');         valid = false; }
    if (!senha)    { setFieldError('seller-password', 'Crie uma senha.');             valid = false; }
    else if (senha.length < 8) { setFieldError('seller-password', 'Mínimo 8 caracteres.'); valid = false; }
    if (senha !== confirmarSenha) { setFieldError('seller-confirm', 'As senhas não coincidem.'); valid = false; }
    if (!nomeLoja) { setFieldError('seller-store',    'Informe o nome da loja.');     valid = false; }
    if (!cpfCnpj)  { setFieldError('seller-cpf',      'Informe CPF ou CNPJ.');        valid = false; }
    if (!telefone) { setFieldError('seller-phone',    'Informe um telefone.');        valid = false; }
    if (!valid) return;

    const btn = document.getElementById('seller-btn');
    btn.disabled = true;
    btn.textContent = 'Cadastrando...';

    try {
        const res  = await fetch('api/vendedor/cadastrar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                nome,
                email,
                senha,
                confirmar_senha: confirmarSenha,
                nome_loja: nomeLoja,
                cpf_cnpj: cpfCnpj,
                telefone,
            }),
        });
        const data = await res.json();

        if (data.sucesso) {
            showBanner('seller-success', data.mensagem, false);
            setTimeout(() => { window.location.href = data.redirect || 'dashboard-vendedor.php'; }, 1000);
        } else {
            if (data.erros) {
                if (data.erros.nome)            setFieldError('seller-name',     data.erros.nome);
                if (data.erros.email)           setFieldError('seller-email',    data.erros.email);
                if (data.erros.senha)           setFieldError('seller-password', data.erros.senha);
                if (data.erros.confirmar_senha) setFieldError('seller-confirm',  data.erros.confirmar_senha);
                if (data.erros.nome_loja)       setFieldError('seller-store',    data.erros.nome_loja);
                if (data.erros.cpf_cnpj)        setFieldError('seller-cpf',      data.erros.cpf_cnpj);
                if (data.erros.telefone)        setFieldError('seller-phone',    data.erros.telefone);
            }
            showBanner('seller-error', data.mensagem || 'Erro ao realizar o cadastro.');
        }
    } catch {
        showBanner('seller-error', 'Erro de conexão. Tente novamente.');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Cadastrar como Vendedor';
    }
});
</script>
</body>
</html>
