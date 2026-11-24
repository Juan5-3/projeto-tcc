<?php
// ============================================================
// ShopBR — API: Remover item do carrinho (remoção total)
// POST /api/carrinho/remover.php
// Body: { "produto_id": 1 }
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

if (!$produtoId) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'produto_id inválido.']);
    exit;
}

try {
    $db        = getDB();
    $usuarioId = (int) $_SESSION['usuario_id'];

    $db->prepare('DELETE FROM carrinho_itens WHERE usuario_id = ? AND produto_id = ?')
       ->execute([$usuarioId, $produtoId]);

    $stmtCount = $db->prepare('SELECT COALESCE(SUM(quantidade),0) AS total FROM carrinho_itens WHERE usuario_id = ?');
    $stmtCount->execute([$usuarioId]);
    $totalItens = (int) $stmtCount->fetch()['total'];

    echo json_encode(['sucesso' => true, 'mensagem' => 'Item removido.', 'total_itens' => $totalItens]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro interno.']);
}
