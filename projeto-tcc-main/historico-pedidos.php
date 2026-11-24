<?php
// ShopBR — Histórico de Pedidos
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';
iniciarSessao();

// Apenas usuários logados
if (!estaLogado()) {
    header('Location: login.php');
    exit;
}

$usuarioId = (int) $_SESSION['usuario_id'];
$pedidos   = [];
$erroDB    = false;

try {
    $db = getDB();

    // Busca todos os pedidos do usuário, do mais recente ao mais antigo
    $stmtPedidos = $db->prepare(
        'SELECT id, total, status, criado_em
         FROM pedidos
         WHERE usuario_id = ?
         ORDER BY criado_em DESC'
    );
    $stmtPedidos->execute([$usuarioId]);
    $listaPedidos = $stmtPedidos->fetchAll();

    if (!empty($listaPedidos)) {
        // Busca todos os itens desses pedidos de uma só vez
        $ids          = array_column($listaPedidos, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $stmtItens = $db->prepare(
            "SELECT pedido_id, produto_id, nome_produto, preco_unitario, quantidade, subtotal
             FROM pedido_itens
             WHERE pedido_id IN ($placeholders)
             ORDER BY id ASC"
        );
        $stmtItens->execute($ids);
        $todosItens = $stmtItens->fetchAll();

        // Agrupa itens por pedido_id
        $itensPorPedido = [];
        foreach ($todosItens as $item) {
            $itensPorPedido[(int) $item['pedido_id']][] = $item;
        }

        // Monta o array final
        foreach ($listaPedidos as $pedido) {
            $pedidos[] = [
                'id'       => (int)    $pedido['id'],
                'total'    => (float)  $pedido['total'],
                'status'   =>          $pedido['status'],
                'criado_em'=>          $pedido['criado_em'],
                'itens'    => $itensPorPedido[(int) $pedido['id']] ?? [],
            ];
        }
    }

} catch (PDOException $e) {
    $erroDB = true;
}

function fmtBRL(float $v): string {
    return 'R$ ' . number_format($v, 2, ',', '.');
}

function fmtData(string $dt): string {
    $ts = strtotime($dt);
    return date('d/m/Y', $ts) . ' às ' . date('H:i', $ts);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopBR — Histórico de Pedidos</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<main>
<section class="orders-page">
    <div class="container">

        <nav class="page-nav">
            <a href="index.php" class="back-link">← Continuar comprando</a>
        </nav>

        <h1 style="font-size:24px;font-weight:700;margin-bottom:24px;">📋 Meu Histórico de Pedidos</h1>

        <?php if ($erroDB): ?>
            <div class="empty-message">
                <div class="empty-icon">⚠️</div>
                <h3>Erro ao carregar pedidos</h3>
                <p>Não foi possível conectar ao banco de dados. Tente novamente.</p>
            </div>

        <?php elseif (empty($pedidos)): ?>
            <div class="empty-message">
                <div class="empty-icon">📦</div>
                <h3>Nenhum pedido ainda</h3>
                <p>Quando você finalizar uma compra, ela aparecerá aqui.</p>
                <a href="index.php" class="btn btn-primary" style="margin-top:16px;">Ver produtos</a>
            </div>

        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($pedidos as $pedido): ?>
                    <div class="order-card">
                        <div class="order-card-header">
                            <div class="order-card-meta">
                                <span class="order-id">Pedido #<?= $pedido['id'] ?></span>
                                <span class="order-date"><?= fmtData($pedido['criado_em']) ?></span>
                            </div>
                            <div class="order-card-right">
                                <span class="order-status order-status--<?= $pedido['status'] ?>">
                                    <?= $pedido['status'] === 'concluido' ? '✅ Concluído' : '❌ Cancelado' ?>
                                </span>
                                <span class="order-total"><?= fmtBRL($pedido['total']) ?></span>
                            </div>
                        </div>

                        <div class="order-items">
                            <?php foreach ($pedido['itens'] as $item): ?>
                                <div class="order-item">
                                    <span class="order-item-name">
                                        <?= htmlspecialchars($item['nome_produto'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                    <span class="order-item-qty">× <?= (int) $item['quantidade'] ?></span>
                                    <span class="order-item-price"><?= fmtBRL((float) $item['preco_unitario']) ?></span>
                                    <span class="order-item-subtotal"><?= fmtBRL((float) $item['subtotal']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="order-card-footer">
                            <span><?= count($pedido['itens']) ?> <?= count($pedido['itens']) === 1 ? 'produto' : 'produtos' ?></span>
                            <strong>Total: <?= fmtBRL($pedido['total']) ?></strong>
                        </div>
                    </div>
                <?php endforeach; ?>
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

<script src="js/main.js"></script>
</body>
</html>
