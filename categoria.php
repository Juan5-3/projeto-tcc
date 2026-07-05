<?php
// ShopBR - Category Page
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

// ── Busca categoria pelo slug ──────────────────────────────
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

$currentCategory = null;

try {
    $db = getDB();
    if ($slug !== '') {
        $stmtCat = $db->prepare('SELECT id, slug, nome, icone FROM categorias WHERE slug = ? LIMIT 1');
        $stmtCat->execute([$slug]);
        $currentCategory = $stmtCat->fetch() ?: null;
    }
} catch (PDOException $e) {
    // BD indisponível — fallback seguro
}

if (!$currentCategory) {
    $currentCategory = ['id' => 0, 'slug' => $slug ?: 'categoria', 'nome' => 'Categoria', 'icone' => '🗂️'];
}

// ── Busca produtos da categoria ───────────────────────────
$products    = [];
$totalCount  = 0;

try {
    $db = getDB();

    if ($currentCategory['id'] > 0) {
        $stmtCount = $db->prepare(
            'SELECT COUNT(*) as total FROM produtos WHERE categoria_id = ? AND ativo = 1'
        );
        $stmtCount->execute([$currentCategory['id']]);
        $totalCount = (int) $stmtCount->fetch()['total'];

        $stmtProd = $db->prepare(
            'SELECT p.id, p.nome, p.preco, p.imagem_url, v.nome_loja,
                    COALESCE(
                        (SELECT o.preco_oferta FROM ofertas o
                         WHERE o.produto_id = p.id AND o.ativa = 1 AND o.data_fim > NOW()
                         ORDER BY o.criado_em DESC LIMIT 1),
                        NULL
                    ) AS preco_oferta,
                    (SELECT o.data_fim FROM ofertas o
                     WHERE o.produto_id = p.id AND o.ativa = 1 AND o.data_fim > NOW()
                     ORDER BY o.criado_em DESC LIMIT 1) AS oferta_data_fim
             FROM produtos p
             INNER JOIN vendedores v ON v.id = p.vendedor_id
             WHERE p.categoria_id = ? AND p.ativo = 1
             ORDER BY p.criado_em DESC'
        );
        $stmtProd->execute([$currentCategory['id']]);
        $products = $stmtProd->fetchAll();

        // Incrementa visualizações de todos os produtos exibidos
        if (!empty($products)) {
            $ids = implode(',', array_map(fn($p) => (int)$p['id'], $products));
            $db->exec("UPDATE produtos SET visualizacoes = visualizacoes + 1 WHERE id IN ({$ids})");
        }
    }
} catch (PDOException $e) {
    // Falha silenciosa — grid ficará vazio
}

/**
 * Formata preço decimal para o padrão BR: R$ 1.299,90
 */
function formatarPreco(float $preco): string {
    return 'R$ ' . number_format($preco, 2, ',', '.');
}

// Variáveis para o header incluso
$headerActivePage   = 'categorias';
$headerBase         = '';
$estaLogadoLocal    = estaLogado();
$ehVendedorLocal    = eVendedor();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopBR — <?= htmlspecialchars($currentCategory['nome'], ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="Explore produtos de <?= htmlspecialchars($currentCategory['nome'], ENT_QUOTES, 'UTF-8') ?> no ShopBR.">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<main>
    <!-- Category header -->
    <div class="category-page-header">
        <div class="container">
            <nav aria-label="Caminho de navegação">
                <a href="index.php" class="back-link">
                    <span aria-hidden="true">←</span> Início
                </a>
            </nav>
            <h1>
                <span aria-hidden="true"><?= $currentCategory['icone'] ?></span>
                <?= htmlspecialchars($currentCategory['nome'], ENT_QUOTES, 'UTF-8') ?>
            </h1>
            <p>
                <?php if ($totalCount > 0): ?>
                    <?= $totalCount ?> <?= $totalCount === 1 ? 'produto encontrado' : 'produtos encontrados' ?>
                <?php else: ?>
                    Nenhum produto disponível nesta categoria ainda.
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Product grid -->
    <section class="section" aria-labelledby="products-heading">
        <div class="container">
            <h2 class="sr-only" id="products-heading">
                Produtos de <?= htmlspecialchars($currentCategory['nome'], ENT_QUOTES, 'UTF-8') ?>
            </h2>

            <?php if (empty($products)): ?>
                <div class="empty-message" role="status">
                    <div class="empty-icon">📦</div>
                    <h3>Nenhum produto aqui ainda</h3>
                    <p>Os vendedores ainda não cadastraram produtos nesta categoria. Volte em breve!</p>
                </div>
            <?php else: ?>
                <div class="product-grid" role="list">
                    <?php foreach ($products as $product):
                        $productId    = (int) $product['id'];
                        $productName  = htmlspecialchars($product['nome'],       ENT_QUOTES, 'UTF-8');
                        $productPrice = formatarPreco((float) $product['preco']);
                        $productStore = htmlspecialchars($product['nome_loja'],  ENT_QUOTES, 'UTF-8');
                        $productImage = $product['imagem_url']
                            ? htmlspecialchars($product['imagem_url'], ENT_QUOTES, 'UTF-8')
                            : null;
                        $categoryName  = htmlspecialchars($currentCategory['nome'], ENT_QUOTES, 'UTF-8');
                        $temOferta     = $product['preco_oferta'] !== null;
                        $precoFinal    = $temOferta ? formatarPreco((float)$product['preco_oferta']) : $productPrice;
                        $comparePrice  = $temOferta ? $precoFinal : $productPrice;
                        $ofertaDataFim = ($temOferta && !empty($product['oferta_data_fim']))
                            ? date('d/m/Y H:i', strtotime($product['oferta_data_fim']))
                            : null;
                    ?>
                        <article class="product-card" role="listitem" aria-label="<?= $productName ?>">
                            <div class="product-image" aria-hidden="true">
                                <?php if ($productImage): ?>
                                    <img src="<?= $productImage ?>" alt="<?= $productName ?>" loading="lazy">
                                <?php else: ?>
                                    📦
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <h3 class="product-name"><?= $productName ?></h3>
                                <div class="product-price-group">
                                    <?php if ($temOferta): ?>
                                        <span class="product-price-original"><?= $productPrice ?></span>
                                        <span class="product-price product-price-offer"><?= $precoFinal ?></span>
                                    <?php else: ?>
                                        <span class="product-price"><?= $productPrice ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($ofertaDataFim): ?>
                                    <p class="offer-expiry-inline">⏱ Oferta até <?= $ofertaDataFim ?></p>
                                <?php endif; ?>
                                <p class="product-store">🏪 <?= $productStore ?></p>
                            </div>
                            <div class="product-card-footer">
                                <div class="product-compare">
                                    <label>
                                        <input
                                            type="checkbox"
                                            class="compare-checkbox"
                                            data-id="<?= $productId ?>"
                                            data-name="<?= $productName ?>"
                                            data-price="<?= $comparePrice ?>"
                                            data-price-original="<?= $temOferta ? $productPrice : '' ?>"
                                            data-category="<?= $categoryName ?>"
                                            data-rating="0"
                                            data-image="<?= htmlspecialchars($product['imagem_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                            aria-label="Adicionar <?= $productName ?> à comparação"
                                        >
                                        Comparar
                                    </label>
                                </div>
                                <?php if ($estaLogadoLocal && !$ehVendedorLocal): ?>
                                    <button
                                        class="btn-add-cart"
                                        data-id="<?= $productId ?>"
                                        data-nome="<?= $productName ?>"
                                        aria-label="Adicionar <?= $productName ?> ao carrinho"
                                    >🛒 Adicionar</button>
                                <?php elseif (!$estaLogadoLocal): ?>
                                    <a href="login.php" class="btn-add-cart btn-add-cart-login">
                                        🔒 Entrar para comprar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
        <div class="comparison-bar-items" id="comparison-bar-items" aria-label="Produtos selecionados para comparação"></div>
        <div class="comparison-bar-actions">
            <button class="btn btn-primary" id="compare-btn" aria-label="Comparar produtos selecionados">
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
                <ul><li><a href="sobre.php">Sobre Nós</a></li></ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> ShopBR. Todos os direitos reservados.</p>
        </div>
    </div>
</footer>

<!-- Toast de feedback do carrinho -->
<div id="cart-toast" role="status" aria-live="polite"></div>

<?php if ($estaLogadoLocal && !$ehVendedorLocal): ?>
<script>
function showToast(msg, ok = true) {
    const t = document.getElementById('cart-toast');
    t.textContent = msg;
    t.className = 'cart-toast ' + (ok ? 'cart-toast-ok' : 'cart-toast-err');
    t.style.display = 'block';
    clearTimeout(t._timer);
    t._timer = setTimeout(() => { t.style.display = 'none'; }, 2800);
}

document.querySelectorAll('.btn-add-cart').forEach(btn => {
    btn.addEventListener('click', async function () {
        const id   = parseInt(this.dataset.id);
        const nome = this.dataset.nome;
        const orig = this.textContent;

        this.disabled    = true;
        this.textContent = '...';

        try {
            const res  = await fetch('api/carrinho/adicionar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ produto_id: id, quantidade: 1 }),
            });
            const data = await res.json();

            if (data.sucesso) {
                showToast('✓ ' + nome + ' adicionado ao carrinho!');
                // Atualiza badge
                const badge = document.getElementById('cart-badge');
                if (badge) {
                    badge.textContent = data.total_itens;
                    badge.classList.remove('cart-badge-hidden');
                }
            } else {
                showToast(data.mensagem || 'Erro ao adicionar.', false);
            }
        } catch {
            showToast('Erro de conexão.', false);
        } finally {
            this.disabled    = false;
            this.textContent = orig;
        }
    });
});
</script>
<?php endif; ?>

<script src="js/main.js"></script>
</body>
</html>
