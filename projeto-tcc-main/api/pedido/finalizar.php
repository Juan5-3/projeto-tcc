<?php
// ============================================================
// ShopBR — API: Finalizar Compra
// POST /api/pedido/finalizar.php
// Cria um pedido a partir dos itens do carrinho do usuário,
// limpa o carrinho e retorna o id do pedido criado.
// ============================================================

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

header('Content-Type: application/json; charset=utf-8');
iniciarSessao();

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

// Usuário deve estar logado
if (!estaLogado()) {
    http_response_code(401);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Não autenticado.']);
    exit;
}

$usuarioId = (int) $_SESSION['usuario_id'];

try {
    $db = getDB();

    // 1. Busca os itens do carrinho com preço final (considera oferta ativa)
    $sql = "
        SELECT
            ci.produto_id,
            ci.quantidade,
            p.nome,
            p.estoque,
            COALESCE(
                (SELECT o.preco_oferta FROM ofertas o
                 WHERE o.produto_id = p.id AND o.ativa = 1 AND o.data_fim > NOW()
                 ORDER BY o.criado_em DESC LIMIT 1),
                p.preco
            ) AS preco_final
        FROM carrinho_itens ci
        INNER JOIN produtos p ON p.id = ci.produto_id
        WHERE ci.usuario_id = ?
        ORDER BY ci.adicionado_em ASC
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([$usuarioId]);
    $itens = $stmt->fetchAll();

    if (empty($itens)) {
        http_response_code(400);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Carrinho vazio.']);
        exit;
    }

    // 2. Valida estoque para todos os itens antes de qualquer escrita
    foreach ($itens as $item) {
        if ((int) $item['quantidade'] > (int) $item['estoque']) {
            echo json_encode([
                'sucesso'  => false,
                'mensagem' => "Estoque insuficiente para \"" . $item['nome'] . "\".",
            ]);
            exit;
        }
    }

    // 3. Calcula total
    $total = 0.0;
    foreach ($itens as $item) {
        $total += (float) $item['preco_final'] * (int) $item['quantidade'];
    }
    $total = round($total, 2);

    // 4. Inicia transação
    $db->beginTransaction();

    // 4a. Cria o pedido
    $stmtPedido = $db->prepare(
        'INSERT INTO pedidos (usuario_id, total, status) VALUES (?, ?, "concluido")'
    );
    $stmtPedido->execute([$usuarioId, $total]);
    $pedidoId = (int) $db->lastInsertId();

    // 4b. Insere os itens do pedido
    $stmtItem = $db->prepare(
        'INSERT INTO pedido_itens (pedido_id, produto_id, nome_produto, preco_unitario, quantidade, subtotal)
         VALUES (?, ?, ?, ?, ?, ?)'
    );

    // 4c. Atualiza estoque de cada produto
    $stmtEstoque = $db->prepare(
        'UPDATE produtos SET estoque = estoque - ? WHERE id = ? AND estoque >= ?'
    );

    foreach ($itens as $item) {
        $subtotal = round((float) $item['preco_final'] * (int) $item['quantidade'], 2);

        $stmtItem->execute([
            $pedidoId,
            (int)   $item['produto_id'],
            $item['nome'],
            (float) $item['preco_final'],
            (int)   $item['quantidade'],
            $subtotal,
        ]);

        $rows = $stmtEstoque->execute([
            (int) $item['quantidade'],
            (int) $item['produto_id'],
            (int) $item['quantidade'],
        ]);

        // Se nenhuma linha foi afetada, estoque mudou entre a validação e o update
        if ($stmtEstoque->rowCount() === 0) {
            $db->rollBack();
            echo json_encode([
                'sucesso'  => false,
                'mensagem' => "Estoque insuficiente para \"" . $item['nome'] . "\". Tente novamente.",
            ]);
            exit;
        }
    }

    // 4d. Limpa o carrinho
    $db->prepare('DELETE FROM carrinho_itens WHERE usuario_id = ?')->execute([$usuarioId]);

    $db->commit();

    echo json_encode([
        'sucesso'   => true,
        'mensagem'  => 'Compra finalizada com sucesso!',
        'pedido_id' => $pedidoId,
    ]);

} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro interno. Tente novamente.']);
}
