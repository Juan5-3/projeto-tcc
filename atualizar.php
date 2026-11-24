<?php
// ============================================================
// ShopBR — API: Atualizar quantidade de item no carrinho
// POST /api/carrinho/atualizar.php
// Body: { "produto_id": 1, "acao": "aumentar" | "diminuir" }
// ============================================================
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

$input     = json_decode(file_get_contents('php://input'), true);
$produtoId = filter_var($input['produto_id'] ?? null, FILTER_VALIDATE_INT);
$acao      = $input['acao'] ?? ''; // 'aumentar' ou 'diminuir'

if (!$produtoId || !in_array($acao, ['aumentar', 'diminuir'], true)) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Parâmetros inválidos.']);
    exit;
}

try {
    $db        = getDB();
    $usuarioId = (int) $_SESSION['usuario_id'];

    // Busca item atual
    $stmtQ = $db->prepare(
        'SELECT ci.quantidade, p.estoque
         FROM carrinho_itens ci
         INNER JOIN produtos p ON p.id = ci.produto_id
         WHERE ci.usuario_id = ? AND ci.produto_id = ? LIMIT 1'
    );
    $stmtQ->execute([$usuarioId, $produtoId]);
    $item = $stmtQ->fetch();

    if (!$item) {
        http_response_code(404);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Item não encontrado no carrinho.']);
        exit;
    }

    $qtdAtual = (int) $item['quantidade'];
    $estoque  = (int) $item['estoque'];

    if ($acao === 'aumentar') {
        if ($qtdAtual >= $estoque) {
            http_response_code(422);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Estoque insuficiente.']);
            exit;
        }
        $db->prepare(
            'UPDATE carrinho_itens SET quantidade = quantidade + 1 WHERE usuario_id = ? AND produto_id = ?'
        )->execute([$usuarioId, $produtoId]);
    } else {
        // diminuir
        if ($qtdAtual <= 1) {
            // Remove o item quando chega a 0
            $db->prepare(
                'DELETE FROM carrinho_itens WHERE usuario_id = ? AND produto_id = ?'
            )->execute([$usuarioId, $produtoId]);
        } else {
            $db->prepare(
                'UPDATE carrinho_itens SET quantidade = quantidade - 1 WHERE usuario_id = ? AND produto_id = ?'
            )->execute([$usuarioId, $produtoId]);
        }
    }

    // Contagem total
    $stmtCount = $db->prepare(
        'SELECT COALESCE(SUM(quantidade), 0) AS total FROM carrinho_itens WHERE usuario_id = ?'
    );
    $stmtCount->execute([$usuarioId]);
    $totalItens = (int) $stmtCount->fetch()['total'];

    echo json_encode([
        'sucesso'     => true,
        'mensagem'    => 'Carrinho atualizado.',
        'total_itens' => $totalItens,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro interno.']);
}
