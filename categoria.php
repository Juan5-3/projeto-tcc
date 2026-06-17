<?php
// ShopBR - Category Page
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

$slug = isset($_GET['slug']) ? htmlspecialchars(trim($_GET['slug']), ENT_QUOTES, 'UTF-8') : '';

// Find current category
$currentCategory = null;
foreach ($categories as $cat) {
    if ($cat['slug'] === $slug) {
        $currentCategory = $cat;
        break;
    }
}

// Fallback if slug not found
if (!$currentCategory) {
    $currentCategory = ['slug' => $slug ?: 'categoria', 'name' => 'Categoria', 'icon' => '🗂️'];
}

$products = [
    ['id' => 1, 'name' => 'Produto Exemplo 1', 'price' => 'R$ 149,90', 'rating' => 4.5, 'image' => '📦'],
    ['id' => 2, 'name' => 'Produto Exemplo 2', 'price' => 'R$ 299,90', 'rating' => 4.0, 'image' => '📦'],
    ['id' => 3, 'name' => 'Produto Exemplo 3', 'price' => 'R$ 89,90',  'rating' => 3.5, 'image' => '📦'],
    ['id' => 4, 'name' => 'Produto Exemplo 4', 'price' => 'R$ 199,90', 'rating' => 5.0, 'image' => '📦'],
    ['id' => 5, 'name' => 'Produto Exemplo 5', 'price' => 'R$ 349,90', 'rating' => 4.2, 'image' => '📦'],
    ['id' => 6, 'name' => 'Produto Exemplo 6', 'price' => 'R$ 59,90',  'rating' => 3.8, 'image' => '📦'],
];

/**
 * Render star rating HTML
 */
function renderStars(float $rating): string {
    $full  = (int) floor($rating);
    $half  = ($rating - $full) >= 0.5 ? 1 : 0;
    $empty = 5 - $full - $half;
    $stars = str_repeat('★', $full)
           . ($half ? '½' : '')
           . str_repeat('☆', $empty);
    return htmlspecialchars($stars, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopBR — <?= htmlspecialchars($currentCategory['name'], ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="Explore produtos de <?= htmlspecialchars($currentCategory['name'], ENT_QUOTES, 'UTF-8') ?> no ShopBR.">
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
            <button class="header-search-btn" aria-label="Buscar">🔍</button>
        </div>

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
            <a href="index.php#ofertas">Ofertas</a>
            <a href="cadastro-vendedor.php">Seja um Vendedor</a>
            <a href="login.php" class="btn btn-primary">Entrar</a>
        </nav>
    </div>
</header>

<main>
    <!-- Category header -->
    <div class="category-page-header">
        <div class="container">
            <nav aria-label="Caminho de navegação">
                <a href="index.php" class="back-link">
                    <span aria-hidden="true">←</span> Início
                </a>
            </nav>
            <h1 aria-live="polite">
                <span aria-hidden="true"><?= $currentCategory['icon'] ?></span>
                <?= htmlspecialchars($currentCategory['name'], ENT_QUOTES, 'UTF-8') ?>
            </h1>
            <p><?= count($products) ?> produtos encontrados</p>
        </div>
    </div>

    <!-- Product grid -->
    <section class="section" aria-labelledby="products-heading">
        <div class="container">
            <h2 class="sr-only" id="products-heading">Produtos de <?= htmlspecialchars($currentCategory['name'], ENT_QUOTES, 'UTF-8') ?></h2>
            <div class="product-grid" role="list">
                <?php foreach ($products as $product):
                    $categoryName = htmlspecialchars($currentCategory['name'], ENT_QUOTES, 'UTF-8');
                    $productId    = (int) $product['id'];
                    $productName  = htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8');
                    $productPrice = htmlspecialchars($product['price'], ENT_QUOTES, 'UTF-8');
                    $productRating = number_format($product['rating'], 1, '.', '');
                    $productImage  = htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8');
                ?>
                    <article class="product-card" role="listitem" aria-label="<?= $productName ?>">
                        <div class="product-image" aria-hidden="true">
                            <?= $product['image'] ?>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?= $productName ?></h3>
                            <p class="product-price"><?= $productPrice ?></p>
                            <div class="product-rating" aria-label="Avaliação: <?= $productRating ?> de 5 estrelas">
                                <span class="stars" aria-hidden="true"><?= renderStars((float)$product['rating']) ?></span>
                                <span>(<?= $productRating ?>)</span>
                            </div>
                        </div>
                        <div class="product-compare">
                            <label>
                                <input
                                    type="checkbox"
                                    class="compare-checkbox"
                                    data-id="<?= $productId ?>"
                                    data-name="<?= $productName ?>"
                                    data-price="<?= $productPrice ?>"
                                    data-category="<?= $categoryName ?>"
                                    data-rating="<?= $productRating ?>"
                                    data-image="<?= $productImage ?>"
                                    aria-label="Adicionar <?= $productName ?> à comparação"
                                >
                                Comparar
                            </label>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<!-- ══ COMPARISON BAR ══ -->
<div
    class="comparison-bar"
    id="comparison-bar"
    role="region"
    aria-label="Barra de comparação de produtos"
    aria-live="polite"
>
    <div class="comparison-bar-inner">
        <span class="comparison-bar-label">Comparar:</span>
        <div class="comparison-bar-items" id="comparison-bar-items" aria-label="Produtos selecionados para comparação">
            <!-- Filled by JS -->
        </div>
        <div class="comparison-bar-actions">
            <button
                class="btn btn-primary"
                id="compare-btn"
                aria-label="Comparar produtos selecionados"
            >
                Comparar
            </button>
            <button
                class="btn btn-secondary"
                id="clear-compare-btn"
                aria-label="Limpar seleção de comparação"
                style="border-color:rgba(255,255,255,0.4);color:rgba(255,255,255,0.8);"
            >
                Limpar
            </button>
        </div>
    </div>
</div>

<!-- ══ FOOTER ══ -->
<footer class="site-footer" role="contentinfo">
    <div class="container">
        <div class="footer-inner">
            <div class="footer-brand">
                <a href="index.php" class="site-logo" aria-label="ShopBR">Shop<span>BR</span></a>
                <p>O marketplace que conecta vendedores e compradores em todo o Brasil.</p>
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
