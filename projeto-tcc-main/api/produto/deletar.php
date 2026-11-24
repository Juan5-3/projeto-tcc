<?php
// ============================================================
// ShopBR — API: Deletar produto
// DELETE /api/produto/deletar.php
// Body: { "produto_id": 1 }
// ============================================================
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

iniciarSessao();
if (!eVendedor()) {
    http_response_code(403);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Acesso restrito a vendedores.']);
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
    $db = getDB();

    $stmtV = $db->prepare('SELECT id FROM vendedores WHERE usuario_id = ? LIMIT 1');
    $stmtV->execute([$_SESSION['usuario_id']]);
    $vendedor = $stmtV->fetch();
    if (!$vendedor) {
        http_response_code(403);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Perfil de vendedor não encontrado.']);
        exit;
    }

    $stmtP = $db->prepare('SELECT id FROM produtos WHERE id = ? AND vendedor_id = ? LIMIT 1');
    $stmtP->execute([$produtoId, $vendedor['id']]);
    if (!$stmtP->fetch()) {
        http_response_code(404);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Produto não encontrado.']);
        exit;
    }

    $db->prepare('DELETE FROM produtos WHERE id = ?')->execute([$produtoId]);

    echo json_encode(['sucesso' => true, 'mensagem' => 'Produto removido com sucesso.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro interno ao remover produto.']);
}
