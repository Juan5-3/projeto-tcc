<?php
// ShopBR - Product Comparison Page
// Products are loaded entirely via JS from sessionStorage
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopBR — Comparar Produtos</title>
    <meta name="description" content="Compare produtos lado a lado no ShopBR.">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- ══ HEADER ══ -->
<header class="site-header" role="banner">
    <div class="container header-inner">
        <a href="index.php" class="site-logo" aria-label="ShopBR — Página inicial">Shop<span>BR</span></a>

        <button
            class="hamburger"
            id="hamburger-btn"
            aria-label="Abrir menu de navegação"
            aria-expanded="false"
            aria-controls="main-nav"
        >
            <span></span><span></span><span></span>
        </button>

        <nav class="header-nav" id="main-nav" role="navigation" aria-label="Navegação principal">
            <a href="index.php">Início</a>
            <a href="index.php#categorias">Categorias</a>
            <a href="login.php">Entrar</a>
            <a href="cadastro-vendedor.php" class="btn btn-primary">Seja um Vendedor</a>
        </nav>
    </div>
</header>

<main>
    <div class="compare-page">
        <div class="container">

            <!-- Back navigation -->
            <nav class="page-nav" aria-label="Navegação">
                <a href="javascript:history.back()" class="back-link">
                    <span aria-hidden="true">←</span> Voltar
                </a>
            </nav>

            <h1>Comparar Produtos</h1>

            <!-- JS will fill this region -->
            <div id="compare-content" aria-live="polite">
                <!-- Loading state -->
                <div class="compare-empty" id="compare-loading">
                    <p>Carregando produtos...</p>
                </div>
            </div>

        </div>
    </div>
</main>

<!-- ══ FOOTER ══ -->
<footer class="site-footer" role="contentinfo" style="padding:var(--spacing-lg) 0;">
    <div class="container">
        <div class="footer-bottom" style="border:none;padding:0;">
            <p>&copy; <?= date('Y') ?> ShopBR. Todos os direitos reservados.</p>
        </div>
    </div>
</footer>

<script src="js/comparar.js"></script>
</body>
</html>
