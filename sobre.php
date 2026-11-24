<?php
// ShopBR - Sobre Nós
require_once __DIR__ . '/config/session.php';
$headerActivePage = '';
$headerBase       = '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopBR — Sobre Nós</title>
    <meta name="description" content="Saiba mais sobre o ShopBR, o marketplace que conecta vendedores e compradores em todo o Brasil.">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<main>
    <div class="sobre-page">
        <div class="container">

            <nav class="page-nav">
                <a href="index.php" class="back-link">← Início</a>
            </nav>

            <div class="sobre-card">
                <h1 class="sobre-title">Sobre o <span>ShopBR</span></h1>

                <!-- ══ Adicione seu texto aqui ══ -->
                <div class="sobre-content">
                    <p>Adicione seu texto aqui.</p>
                </div>
                <!-- ══ Fim do conteúdo ══ -->

            </div>
        </div>
    </div>
</main>

<footer class="site-footer" role="contentinfo">
    <div class="container">
        <div class="footer-inner">
            <div class="footer-brand">
                <a href="index.php" class="site-logo">Shop<span>BR</span></a>
                <p>O marketplace que conecta vendedores e compradores em todo o Brasil.</p>
            </div>
            <div class="footer-links">
                <h4>Links Úteis</h4>
                <ul><li><a href="sobre.php">Sobre Nós</a></li></ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> ShopBR. Todos os direitos reservados.</p>
        </div>
    </div>
</footer>

<script src="js/main.js"></script>
</body>
</html>
