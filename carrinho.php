<?php
// ShopBR - Carrinho de Compras
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';
iniciarSessao();

$headerBase = '';

// Carrega os itens do carrinho diretamente no PHP (mesma sessão, sem fetch)
$itensCarrinho = [];
$subtotal      = 0.0;
$totalQtd      = 0;
$erroCarrinho  = false;

if (estaLogado()) {
    try {
        $db        = getDB();
        $usuarioId = (int) $_SESSION['usuario_id'];

        $sql = "
            SELECT
                ci.produto_id,
                ci.quantidade,
                p.nome,
                p.preco,
                p.imagem_url,
                p.estoque,
                COALESCE(
                    (SELECT o.preco_oferta FROM ofertas o
                     WHERE o.produto_id = p.id AND o.ativa = 1 AND o.data_fim > NOW()
                     ORDER BY o.criado_em DESC LIMIT 1),
                    p.preco
                ) AS preco_final,
                c.nome  AS categoria_nome,
                v.nome_loja
            FROM carrinho_itens ci
            INNER JOIN produtos   p ON p.id = ci.produto_id
            INNER JOIN categorias c ON c.id = p.categoria_id
            INNER JOIN vendedores v ON v.id = p.vendedor_id
            WHERE ci.usuario_id = ?
            ORDER BY ci.adicionado_em DESC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([$usuarioId]);
        $itensCarrinho = $stmt->fetchAll();

        foreach ($itensCarrinho as &$item) {
            $item['produto_id']  = (int)   $item['produto_id'];
            $item['quantidade']  = (int)   $item['quantidade'];
            $item['preco']       = (float) $item['preco'];
            $item['preco_final'] = (float) $item['preco_final'];
            $item['estoque']     = (int)   $item['estoque'];
            $subtotal += $item['preco_final'] * $item['quantidade'];
            $totalQtd += $item['quantidade'];
        }
        unset($item);
        $subtotal = round($subtotal, 2);

    } catch (PDOException $e) {
        $erroCarrinho = true;
    }
}

function fmtBRL(float $v): string {
    return 'R$ ' . number_format($v, 2, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopBR — Carrinho</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<main>
<section class="cart-page">
    <div class="container">

        <nav class="page-nav">
            <a href="index.php" class="back-link">← Continuar comprando</a>
        </nav>

        <h1 style="font-size:24px;font-weight:700;margin-bottom:24px;">🛒 Meu Carrinho</h1>

        <?php if (!estaLogado()): ?>
            <div class="empty-message">
                <div class="empty-icon">🔒</div>
                <h3>Faça login para ver seu carrinho</h3>
                <p>Sua sacola de compras fica salva na sua conta.</p>
                <a href="login.php" class="btn btn-primary" style="margin-top:16px;">Entrar</a>
            </div>

        <?php elseif ($erroCarrinho): ?>
            <div class="empty-message">
                <div class="empty-icon">⚠️</div>
                <h3>Erro ao carregar carrinho</h3>
                <p>Não foi possível conectar ao banco de dados. Tente novamente.</p>
            </div>

        <?php elseif (empty($itensCarrinho)): ?>
            <div class="empty-message">
                <div class="empty-icon">🛒</div>
                <h3>Carrinho vazio</h3>
                <p>Explore nossas categorias e adicione produtos!</p>
                <a href="index.php" class="btn btn-primary" style="margin-top:16px;">Ver produtos</a>
            </div>

        <?php else: ?>
            <div id="cart-feedback" role="alert" aria-live="polite" style="display:none;margin-bottom:16px;"></div>
            <div class="cart-layout">

                <!-- Itens -->
                <div class="cart-items" id="cart-items-list">
                    <?php foreach ($itensCarrinho as $item):
                        $temOferta = $item['preco_final'] < $item['preco'];
                        $imgUrl    = $item['imagem_url']
                            ? htmlspecialchars($item['imagem_url'], ENT_QUOTES, 'UTF-8')
                            : null;
                        $nomeProd  = htmlspecialchars($item['nome'],         ENT_QUOTES, 'UTF-8');
                        $nomeLoja  = htmlspecialchars($item['nome_loja'],    ENT_QUOTES, 'UTF-8');
                        $nomeCat   = htmlspecialchars($item['categoria_nome'], ENT_QUOTES, 'UTF-8');
                    ?>
                        <div class="cart-item" id="cart-item-<?= $item['produto_id'] ?>">
                            <div class="cart-item-img">
                                <?php if ($imgUrl): ?>
                                    <img src="<?= $imgUrl ?>" alt="<?= $nomeProd ?>" loading="lazy">
                                <?php else: ?>
                                    📦
                                <?php endif; ?>
                            </div>
                            <div class="cart-item-info">
                                <h3 class="cart-item-name"><?= $nomeProd ?></h3>
                                <p class="cart-item-store">🏪 <?= $nomeLoja ?></p>
                                <p class="cart-item-cat"><?= $nomeCat ?></p>
                                <div class="cart-item-price">
                                    <?php if ($temOferta): ?>
                                        <span class="price-original"><?= fmtBRL($item['preco']) ?></span>
                                    <?php endif; ?>
                                    <span class="price-final"><?= fmtBRL($item['preco_final']) ?></span>
                                </div>
                            </div>
                            <div class="cart-item-controls">
                                <div class="qty-controls">
                                    <button
                                        class="qty-btn"
                                        onclick="alterarQtd(<?= $item['produto_id'] ?>, 'diminuir')"
                                        aria-label="Diminuir quantidade de <?= $nomeProd ?>"
                                    >−</button>
                                    <span class="qty-value" id="qty-<?= $item['produto_id'] ?>"><?= $item['quantidade'] ?></span>
                                    <button
                                        class="qty-btn"
                                        onclick="alterarQtd(<?= $item['produto_id'] ?>, 'aumentar')"
                                        aria-label="Aumentar quantidade de <?= $nomeProd ?>"
                                    >+</button>
                                </div>
                                <button
                                    class="btn-remove-item"
                                    onclick="removerItem(<?= $item['produto_id'] ?>)"
                                    aria-label="Remover <?= $nomeProd ?> do carrinho"
                                >🗑️ Remover</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Resumo -->
                <aside class="cart-summary">
                    <h2>Resumo</h2>
                    <div class="cart-summary-row">
                        <span>Subtotal (<?= $totalQtd ?> <?= $totalQtd === 1 ? 'item' : 'itens' ?>)</span>
                        <strong><?= fmtBRL($subtotal) ?></strong>
                    </div>
                    <hr style="border:none;border-top:1px solid var(--color-border);margin:16px 0;">
                    <div class="cart-summary-row cart-summary-total">
                        <span>Total</span>
                        <strong><?= fmtBRL($subtotal) ?></strong>
                    </div>
                    <button
                        class="btn btn-primary btn-block"
                        id="btn-finalizar"
                        style="margin-top:20px;"
                        onclick="finalizarCompra()"
                    >
                        ✅ Finalizar Compra
                    </button>
                    <a
                        href="historico-pedidos.php"
                        style="display:block;text-align:center;font-size:13px;margin-top:12px;color:var(--color-primary);"
                    >📋 Ver histórico de pedidos</a>
                </aside>

            </div>
        <?php endif; ?>

    </div>
</section>
</main>

<!-- ══ FOOTER ══ -->
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

<?php if (estaLogado() && !empty($itensCarrinho)): ?>
<script>
function showFeedback(msg, ok = false) {
    const el = document.getElementById('cart-feedback');
    if (!el) return;
    el.textContent = msg;
    el.style.display   = 'block';
    el.style.padding   = '10px 14px';
    el.style.borderRadius = '8px';
    el.style.fontSize  = '14px';
    el.style.fontWeight = '600';
    el.style.background = ok ? '#e8f5e9' : '#fdecea';
    el.style.color      = ok ? '#2e7d32' : '#c62828';
    el.style.border     = ok ? '1px solid #a5d6a7' : '1px solid #ef9a9a';
}

async function alterarQtd(produtoId, acao) {
    try {
        const res  = await fetch('api/carrinho/atualizar.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            credentials: 'same-origin',
            body: JSON.stringify({ produto_id: produtoId, acao: acao }),
        });
        const data = await res.json();
        if (data.sucesso) {
            location.reload();
        } else {
            showFeedback(data.mensagem || 'Erro ao atualizar.');
        }
    } catch {
        showFeedback('Erro de conexão.');
    }
}

async function removerItem(produtoId) {
    try {
        const res  = await fetch('api/carrinho/remover.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            credentials: 'same-origin',
            body: JSON.stringify({ produto_id: produtoId }),
        });
        const data = await res.json();
        if (data.sucesso) {
            location.reload();
        } else {
            showFeedback(data.mensagem || 'Erro ao remover.');
        }
    } catch {
        showFeedback('Erro de conexão.');
    }
}

async function finalizarCompra() {
    const btn = document.getElementById('btn-finalizar');
    if (btn) {
        btn.disabled    = true;
        btn.textContent = '⏳ Processando…';
    }

    try {
        const res  = await fetch('api/pedido/finalizar.php', {
            method: 'POST',
            credentials: 'same-origin',
        });
        const data = await res.json();

        if (data.sucesso) {
            showFeedback('✅ ' + data.mensagem, true);
            // Redireciona ao histórico após 1,5 s
            setTimeout(() => {
                window.location.href = 'historico-pedidos.php';
            }, 1500);
        } else {
            showFeedback(data.mensagem || 'Erro ao finalizar compra.');
            if (btn) {
                btn.disabled    = false;
                btn.textContent = '✅ Finalizar Compra';
            }
        }
    } catch {
        showFeedback('Erro de conexão. Tente novamente.');
        if (btn) {
            btn.disabled    = false;
            btn.textContent = '✅ Finalizar Compra';
        }
    }
}
</script>
<?php endif; ?>

<script src="js/main.js"></script>
</body>
</html>
