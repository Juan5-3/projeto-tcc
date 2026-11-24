<?php
// ShopBR - Home Page
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

$categories = [];
$ofertas    = [];

try {
    $db = getDB();

    // Categorias
    $stmt = $db->query('SELECT slug, nome AS name, icone AS icon FROM categorias ORDER BY id');
    $categories = $stmt->fetchAll();

    // Ofertas do dia — as 8 mais visualizadas com oferta ativa
    $stmtO = $db->query("
        SELECT
            o.id           AS oferta_id,
            o.desconto_pct,
            o.preco_oferta,
            o.data_fim,
            p.id           AS produto_id,
            p.nome         AS produto_nome,
            p.preco        AS preco_original,
            p.imagem_url,
            c.slug         AS categoria_slug,
            v.nome_loja
        FROM ofertas o
        INNER JOIN produtos   p ON p.id = o.produto_id
        INNER JOIN categorias c ON c.id = p.categoria_id
        INNER JOIN vendedores v ON v.id = p.vendedor_id
        WHERE o.ativa = 1 AND o.data_fim > NOW() AND p.ativo = 1
        ORDER BY p.visualizacoes DESC, o.criado_em DESC
        LIMIT 8
    ");
    $ofertas = $stmtO->fetchAll();

} catch (PDOException $e) {
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
}

function fmtPreco(float $v): string {
    return 'R$ ' . number_format($v, 2, ',', '.');
}

$headerActivePage = 'inicio';
$headerBase       = '';
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

<?php require_once __DIR__ . '/includes/header.php'; ?>

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

    <!-- ══ OFFERS ══ -->
    <section class="section" id="ofertas" aria-labelledby="offers-heading" style="background:var(--color-surface);border-top:1px solid var(--color-border);border-bottom:1px solid var(--color-border);">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title" id="offers-heading">Ofertas do Dia</h2>
                <?php if (!empty($ofertas)): ?>
                    <a href="busca.php?q=" class="text-muted" style="font-size:13px;">Ver todos os produtos</a>
                <?php endif; ?>
            </div>

            <?php if (empty($ofertas)): ?>
                <div class="empty-message" role="status">
                    <div class="empty-icon">🏷️</div>
                    <h3>Ofertas em breve</h3>
                    <p>Novas promoções chegando! Fique de olho.</p>
                </div>
            <?php else: ?>
                <div class="offers-grid" role="list">
                    <?php foreach ($ofertas as $of):
                        $nomeProd    = htmlspecialchars($of['produto_nome'], ENT_QUOTES, 'UTF-8');
                        $catSlug     = htmlspecialchars($of['categoria_slug'], ENT_QUOTES, 'UTF-8');
                        $loja        = htmlspecialchars($of['nome_loja'], ENT_QUOTES, 'UTF-8');
                        $desconto    = (int) $of['desconto_pct'];
                        $precoOrig   = fmtPreco((float) $of['preco_original']);
                        $precoOferta = fmtPreco((float) $of['preco_oferta']);
                        $imgUrl      = $of['imagem_url'] ? htmlspecialchars($of['imagem_url'], ENT_QUOTES, 'UTF-8') : null;
                        $dataFim     = date('d/m H:i', strtotime($of['data_fim']));
                    ?>
                        <article class="offer-card" role="listitem" aria-label="<?= $nomeProd ?>">
                            <a href="categoria.php?slug=<?= $catSlug ?>" class="offer-card-link">
                                <div class="offer-badge">-<?= $desconto ?>%</div>
                                <div class="offer-image" aria-hidden="true">
                                    <?php if ($imgUrl): ?>
                                        <img src="<?= $imgUrl ?>" alt="<?= $nomeProd ?>" loading="lazy">
                                    <?php else: ?>
                                        📦
                                    <?php endif; ?>
                                </div>
                                <div class="offer-info">
                                    <h3 class="offer-name"><?= $nomeProd ?></h3>
                                    <p class="offer-store">🏪 <?= $loja ?></p>
                                    <div class="offer-prices">
                                        <span class="offer-price-original"><?= $precoOrig ?></span>
                                        <span class="offer-price-final"><?= $precoOferta ?></span>
                                    </div>
                                    <p class="offer-expiry">⏱ Até <?= $dataFim ?></p>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
                    <li><a href="sobre.php">Sobre Nós</a></li>
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
