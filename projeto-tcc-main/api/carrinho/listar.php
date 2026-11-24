<?php
// ============================================================
// ShopBR — API: Listar itens do carrinho
// GET /api/carrinho/listar.php
// ============================================================
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

iniciarSessao();
if (!estaLogado()) {
    http_response_code(401);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Não autenticado.']);
    exit;
}

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
            -- Preço com oferta ativa, se houver
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
    $itens = $stmt->fetchAll();

    $subtotal = 0.0;
    foreach ($itens as &$item) {
        $item['produto_id']  = (int)   $item['produto_id'];
        $item['quantidade']  = (int)   $item['quantidade'];
        $item['preco']       = (float) $item['preco'];
        $item['preco_final'] = (float) $item['preco_final'];
        $item['estoque']     = (int)   $item['estoque'];
        $subtotal += $item['preco_final'] * $item['quantidade'];
    }
    unset($item);

    echo json_encode([
        'sucesso'   => true,
        'itens'     => $itens,
        'subtotal'  => round($subtotal, 2),
        'total_qtd' => array_sum(array_column($itens, 'quantidade')),
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro interno ao buscar carrinho.']);
}
