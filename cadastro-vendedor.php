<?php
// ShopBR - Seller Registration Page
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

            <!-- Breadcrumb / back -->
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

                <div class="form-success" id="seller-success" role="alert" aria-live="polite">
                    Cadastro disponível em breve.
                </div>

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

                    <button type="submit" class="btn btn-primary btn-block" style="margin-top:8px;">
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
</body>
</html>
