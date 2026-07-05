<?php
// ============================================================
// ShopBR — API: Ativar / Desativar produto
// POST /api/produto/toggle-status.php
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

    // Busca produto verificando propriedade
    $stmtP = $db->prepare('SELECT id, ativo FROM produtos WHERE id = ? AND vendedor_id = ? LIMIT 1');
    $stmtP->execute([$produtoId, $vendedor['id']]);
    $produto = $stmtP->fetch();
    if (!$produto) {
        http_response_code(404);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Produto não encontrado.']);
        exit;
    }

    $novoStatus = $produto['ativo'] ? 0 : 1;
    $db->prepare('UPDATE produtos SET ativo = ? WHERE id = ?')->execute([$novoStatus, $produtoId]);

    echo json_encode([
        'sucesso'  => true,
        'mensagem' => $novoStatus ? 'Produto ativado.' : 'Produto desativado.',
        'ativo'    => (bool) $novoStatus,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro interno.']);
}
