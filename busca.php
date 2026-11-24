<?php
// ShopBR - Página de resultados de busca
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

$query      = trim($_GET['q'] ?? '');
$categoriaSlug = trim($_GET['categoria'] ?? '');
$pagina     = max(1, (int) ($_GET['pagina'] ?? 1));
$porPagina  = 12;
$offset     = ($pagina - 1) * $porPagina;

$products   = [];
$totalCount = 0;
$categorias = [];
$categoriaAtual = null;

/**
 * Formata preço decimal para o padrão BR: R$ 1.299,90
 */
function formatarPreco(float $preco): string {
    return 'R$ ' . number_format($preco, 2, ',', '.');
}

try {
    $db = getDB();

    // Carrega lista de categorias para o filtro lateral
    $categorias = $db->query('SELECT slug, nome, icone FROM categorias ORDER BY nome')->fetchAll();

    // Resolve categoria selecionada
    if ($categoriaSlug !== '') {
        $stmtCat = $db->prepare('SELECT id, slug, nome, icone FROM categorias WHERE slug = ? LIMIT 1');
        $stmtCat->execute([$categoriaSlug]);
        $categoriaAtual = $stmtCat->fetch() ?: null;
    }

    // Monta filtros dinâmicos
    $where  = ['p.ativo = 1'];
    $params = [];

    if ($query !== '') {
        $where[]  = '(p.nome LIKE ? OR p.descricao LIKE ?)';
        $termo    = '%' . $query . '%';
        $params[] = $termo;
        $params[] = $termo;
    }

    if ($categoriaAtual) {
        $where[]  = 'p.categoria_id = ?';
        $params[] = (int) $categoriaAtual['id'];
    }

    $whereClause = implode(' AND ', $where);

    // Total para paginação
    $stmtCount = $db->prepare("SELECT COUNT(*) AS total FROM produtos p WHERE {$whereClause}");
    $stmtCount->execute($params);
    $totalCount = (int) $stmtCount->fetch()['total'];
    $totalPaginas = (int) ceil($totalCount / $porPagina);

    // Produtos
    $sql = "
        SELECT
            p.id, p.nome, p.preco, p.imagem_url,
            c.nome AS categoria_nome, c.slug AS categoria_slug,
            v.nome_loja,
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
        INNER JOIN categorias c ON c.id = p.categoria_id
        INNER JOIN vendedores  v ON v.id = p.vendedor_id
        WHERE {$whereClause}
        ORDER BY p.criado_em DESC
        LIMIT ? OFFSET ?
    ";
    $stmtProd = $db->prepare($sql);
    $stmtProd->execute(array_merge($params, [$porPagina, $offset]));
    $products = $stmtProd->fetchAll();

    // Incrementa visualizações dos produtos exibidos
    if (!empty($products)) {
        $ids = implode(',', array_map(fn($p) => (int)$p['id'], $products));
        $db->exec("UPDATE produtos SET visualizacoes = visualizacoes + 1 WHERE id IN ({$ids})");
    }

} catch (PDOException $e) {
    // BD indisponível — mostrará mensagem de erro abaixo
}

$queryEsc           = htmlspecialchars($query, ENT_QUOTES, 'UTF-8');
$headerSearchValue  = $query;
$headerActivePage   = '';
$headerBase         = '';
$estaLogadoLocal    = estaLogado();
$ehVendedorLocal    = eVendedor();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopBR — Busca<?= $query !== '' ? ': ' . $queryEsc : '' ?></title>
    <meta name="description" content="Resultados de busca no ShopBR<?= $query !== '' ? ' para "' . $queryEsc . '"' : '' ?>.">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<main>
    <div class="search-page">
        <div class="container">

            <!-- Breadcrumb -->
            <nav class="page-nav" aria-label="Navegação">
                <a href="index.php" class="back-link">← Início</a>
            </nav>

            <!-- Título dos resultados -->
            <div class="search-header">
                <h1 class="search-title">
                    <?php if ($query !== ''): ?>
                        Resultados para <em>"<?= $queryEsc ?>"</em>
                    <?php else: ?>
                        Todos os produtos
                    <?php endif; ?>
                </h1>
                <p class="search-count">
                    <?= $totalCount ?> <?= $totalCount === 1 ? 'produto encontrado' : 'produtos encontrados' ?>
                    <?php if ($categoriaAtual): ?>
                        em <strong><?= htmlspecialchars($categoriaAtual['nome'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <?php endif; ?>
                </p>
            </div>

            <div class="search-layout">

                <!-- ── Filtro lateral de categorias ── -->
                <aside class="search-sidebar" aria-label="Filtrar por categoria">
                    <h2 class="sidebar-title">Categorias</h2>
                    <ul class="sidebar-cat-list">
                        <li>
                            <a
                                href="busca.php?q=<?= urlencode($query) ?>"
                                class="sidebar-cat-item <?= $categoriaSlug === '' ? 'active' : '' ?>"
                            >
                                🗂️ Todas
                            </a>
                        </li>
                        <?php foreach ($categorias as $cat): ?>
                            <li>
                                <a
                                    href="busca.php?q=<?= urlencode($query) ?>&categoria=<?= urlencode($cat['slug']) ?>"
                                    class="sidebar-cat-item <?= $cat['slug'] === $categoriaSlug ? 'active' : '' ?>"
                                >
                                    <?= $cat['icone'] ?>
                                    <?= htmlspecialchars($cat['nome'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </aside>

                <!-- ── Grid de produtos ── -->
                <div class="search-results">
                    <?php if (empty($products)): ?>
                        <div class="empty-message" role="status">
                            <div class="empty-icon">🔍</div>
                            <h3>Nenhum produto encontrado</h3>
                            <?php if ($query !== ''): ?>
                                <p>Não encontramos nada para "<strong><?= $queryEsc ?></strong>". Tente outras palavras ou explore as categorias.</p>
                            <?php else: ?>
                                <p>Nenhum produto cadastrado ainda. Volte em breve!</p>
                            <?php endif; ?>
                            <a href="index.php" class="btn btn-primary" style="margin-top:16px;">Ver categorias</a>
                        </div>
                    <?php else: ?>
                        <div class="product-grid" role="list">
                            <?php foreach ($products as $product):
                                $productId    = (int) $product['id'];
                                $productName  = htmlspecialchars($product['nome'],           ENT_QUOTES, 'UTF-8');
                                $productPrice = formatarPreco((float) $product['preco']);
                                $productStore = htmlspecialchars($product['nome_loja'],      ENT_QUOTES, 'UTF-8');
                                $productCat   = htmlspecialchars($product['categoria_nome'], ENT_QUOTES, 'UTF-8');
                                $productImage = $product['imagem_url']
                                    ? htmlspecialchars($product['imagem_url'], ENT_QUOTES, 'UTF-8')
                                    : null;
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
                                        <a href="categoria.php?slug=<?= urlencode($product['categoria_slug']) ?>" class="product-cat-tag">
                                            <?= $productCat ?>
                                        </a>
                                    </div>
                                    <div class="product-card-footer">
                                        <?php if ($estaLogadoLocal && !$ehVendedorLocal): ?>
                                            <button class="btn-add-cart" data-id="<?= $productId ?>" data-nome="<?= $productName ?>">
                                                🛒 Adicionar
                                            </button>
                                        <?php elseif (!$estaLogadoLocal): ?>
                                            <a href="login.php" class="btn-add-cart btn-add-cart-login">🔒 Entrar para comprar</a>
                                        <?php else: ?>
                                            <span></span>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>

                        <!-- Paginação -->
                        <?php if ($totalPaginas > 1): ?>
                            <nav class="pagination" aria-label="Navegação de páginas">
                                <?php if ($pagina > 1): ?>
                                    <a
                                        href="busca.php?q=<?= urlencode($query) ?>&categoria=<?= urlencode($categoriaSlug) ?>&pagina=<?= $pagina - 1 ?>"
                                        class="btn btn-secondary btn-sm"
                                        aria-label="Página anterior"
                                    >← Anterior</a>
                                <?php endif; ?>

                                <span class="pagination-info">
                                    Página <?= $pagina ?> de <?= $totalPaginas ?>
                                </span>

                                <?php if ($pagina < $totalPaginas): ?>
                                    <a
                                        href="busca.php?q=<?= urlencode($query) ?>&categoria=<?= urlencode($categoriaSlug) ?>&pagina=<?= $pagina + 1 ?>"
                                        class="btn btn-secondary btn-sm"
                                        aria-label="Próxima página"
                                    >Próxima →</a>
                                <?php endif; ?>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

            </div><!-- /.search-layout -->
        </div>
    </div>
</main>

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
        this.disabled = true; this.textContent = '...';
        try {
            const res  = await fetch('api/carrinho/adicionar.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ produto_id: id, quantidade: 1 }),
            });
            const data = await res.json();
            if (data.sucesso) {
                showToast('✓ ' + nome + ' adicionado ao carrinho!');
                const badge = document.getElementById('cart-badge');
                if (badge) { badge.textContent = data.total_itens; badge.classList.remove('cart-badge-hidden'); }
            } else {
                showToast(data.mensagem || 'Erro ao adicionar.', false);
            }
        } catch { showToast('Erro de conexão.', false); }
        finally { this.disabled = false; this.textContent = orig; }
    });
});
</script>
<?php endif; ?>

<script src="js/main.js"></script>
</body>
</html>
