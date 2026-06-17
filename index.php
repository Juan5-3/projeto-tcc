<?php
// ShopBR - Home Page
$categories = [
    ['slug' => 'eletronicos',  'name' => 'Eletrônicos',   'icon' => '📱'],
    ['slug' => 'roupas',       'name' => 'Roupas',         'icon' => '👗'],
    ['slug' => 'casa',         'name' => 'Casa e Jardim',  'icon' => '🏠'],
    ['slug' => 'esportes',     'name' => 'Esportes',       'icon' => '⚽'],
    ['slug' => 'livros',       'name' => 'Livros',         'icon' => '📚'],
    ['slug' => 'beleza',       'name' => 'Beleza',         'icon' => '💄'],
    ['slug' => 'brinquedos',   'name' => 'Brinquedos',     'icon' => '🧸'],
    ['slug' => 'informatica',  'name' => 'Informática',    'icon' => '💻'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopBR — O melhor marketplace do Brasil</title>
    <meta name="description" content="ShopBR: encontre eletrônicos, roupas, casa e jardim, esportes, livros e muito mais.">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- ══ HEADER ══ -->
<header class="site-header" role="banner">
    <div class="container header-inner">
        <a href="index.php" class="site-logo" aria-label="ShopBR — Página inicial">Shop<span>BR</span></a>

        <div class="header-search" role="search">
            <label for="search-input" class="sr-only">Buscar produtos</label>
            <input
                type="search"
                id="search-input"
                name="q"
                placeholder="Buscar produtos..."
                aria-label="Campo de busca"
                autocomplete="off"
            >
            <button class="header-search-btn" aria-label="Buscar">
                🔍
            </button>
        </div>

        <button
            class="hamburger"
            id="hamburger-btn"
            aria-label="Abrir menu de navegação"
            aria-expanded="false"
            aria-controls="main-nav"
        >
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav class="header-nav" id="main-nav" role="navigation" aria-label="Navegação principal">
            <a href="index.php" aria-current="page">Início</a>
            <a href="#categorias">Categorias</a>
            <a href="#ofertas">Ofertas</a>
            <a href="cadastro-vendedor.php">Seja um Vendedor</a>
            <a href="login.php" class="btn btn-primary" aria-label="Cadastre-se como vendedor">
                Entrar
            </a>
        </nav>
    </div>
</header>

<main>
    <!-- ══ HERO ══ -->
    <section class="hero" aria-label="Banner principal">
        <div class="container">
            <h1 class="hero-title">Bem-vindo ao <span>ShopBR</span></h1>
            <p class="hero-subtitle">
                O marketplace que conecta você aos melhores vendedores do Brasil.
                Encontre tudo o que precisa com praticidade e segurança.
            </p>
            <div class="hero-actions">
                <a href="#categorias" class="btn btn-primary">Explorar Categorias</a>
                <a href="cadastro-vendedor.php" class="btn btn-secondary" style="border-color:#FFFFFF;color:#FFFFFF;" onmouseover="this.style.background='#FFFFFF';this.style.color='#1A1A2E';" onmouseout="this.style.background='transparent';this.style.color='#FFFFFF';">
                    Quero Vender
                </a>
            </div>
        </div>
    </section>

    <!-- ══ CATEGORIES ══ -->
    <section class="section" id="categorias" aria-labelledby="categories-heading">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title" id="categories-heading">Categorias</h2>
                <a href="#categorias" class="text-muted" style="font-size:13px;">Ver todas</a>
            </div>

            <?php if (empty($categories)): ?>
                <div class="empty-message" role="status">
                    <div class="empty-icon">🗂️</div>
                    <h3>Nenhuma categoria disponível</h3>
                    <p>As categorias serão carregadas em breve. Volte mais tarde!</p>
                </div>
            <?php else: ?>
                <div class="category-grid" role="list">
                    <?php foreach ($categories as $cat): ?>
                        <a
                            href="categoria.php?slug=<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>"
                            class="category-card"
                            role="listitem"
                            aria-label="Ver produtos de <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>"
                        >
                            <span class="category-icon" aria-hidden="true"><?= $cat['icon'] ?></span>
                            <span class="category-name"><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ══ OFFERS PLACEHOLDER ══ -->
    <section class="section" id="ofertas" aria-labelledby="offers-heading" style="background:var(--color-surface);border-top:1px solid var(--color-border);border-bottom:1px solid var(--color-border);">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title" id="offers-heading">Ofertas do Dia</h2>
            </div>
            <div class="empty-message" role="status">
                <div class="empty-icon">🏷️</div>
                <h3>Ofertas em breve</h3>
                <p>Novas promoções chegando! Fique de olho.</p>
            </div>
        </div>
    </section>

    <!-- ══ CTA VENDEDOR ══ -->
    <section class="section" aria-labelledby="cta-heading">
        <div class="container" style="text-align:center;">
            <h2 id="cta-heading" style="font-size:28px;font-weight:800;margin-bottom:12px;">
                Quer vender no ShopBR?
            </h2>
            <p style="color:var(--color-text-muted);margin-bottom:24px;max-width:480px;margin-left:auto;margin-right:auto;">
                Cadastre sua loja agora e comece a vender para milhões de clientes em todo o Brasil.
            </p>
            <a href="cadastro-vendedor.php" class="btn btn-primary" style="font-size:16px;padding:14px 32px;">
                Cadastrar como Vendedor
            </a>
        </div>
    </section>
</main>

<!-- ══ FOOTER ══ -->
<footer class="site-footer" role="contentinfo">
    <div class="container">
        <div class="footer-inner">
            <div class="footer-brand">
                <a href="index.php" class="site-logo" aria-label="ShopBR — Página inicial">Shop<span>BR</span></a>
                <p>O marketplace que conecta vendedores e compradores em todo o Brasil com segurança e praticidade.</p>
            </div>
            <div class="footer-links">
                <h4>Links Úteis</h4>
                <ul>
                    <li><a href="#">Sobre Nós</a></li>
                    <li><a href="#">Contato</a></li>
                    <li><a href="#">Termos de Uso</a></li>
                    <li><a href="#">Política de Privacidade</a></li>
                </ul>
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
