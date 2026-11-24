<?php
// ============================================================
// ShopBR — API: Adicionar item ao carrinho
// POST /api/carrinho/adicionar.php
// Requer: usuário logado (qualquer tipo)
// Body: { "produto_id": 1, "quantidade": 1 }
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
    echo json_encode(['sucesso' => false, 'mensagem' => 'Faça login para adicionar ao carrinho.']);
    exit;
}

$input      = json_decode(file_get_contents('php://input'), true);
$produtoId  = filter_var($input['produto_id'] ?? null, FILTER_VALIDATE_INT);
$quantidade = max(1, (int) ($input['quantidade'] ?? 1));

if (!$produtoId || $produtoId <= 0) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'produto_id inválido.']);
    exit;
}

try {
    $db = getDB();

    // Verifica se produto existe e está ativo
    $stmtP = $db->prepare('SELECT id, estoque FROM produtos WHERE id = ? AND ativo = 1 LIMIT 1');
    $stmtP->execute([$produtoId]);
    $produto = $stmtP->fetch();
    if (!$produto) {
        http_response_code(404);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Produto não encontrado ou indisponível.']);
        exit;
    }

    if ($produto['estoque'] < $quantidade) {
        http_response_code(422);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Estoque insuficiente.']);
        exit;
    }

    $usuarioId = (int) $_SESSION['usuario_id'];

    // INSERT ... ON DUPLICATE KEY: se já existe, soma a quantidade
    $stmt = $db->prepare(
        'INSERT INTO carrinho_itens (usuario_id, produto_id, quantidade)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE quantidade = quantidade + VALUES(quantidade)'
    );
    $stmt->execute([$usuarioId, $produtoId, $quantidade]);

    // Contagem total de itens no carrinho
    $stmtCount = $db->prepare('SELECT SUM(quantidade) AS total FROM carrinho_itens WHERE usuario_id = ?');
    $stmtCount->execute([$usuarioId]);
    $totalItens = (int) $stmtCount->fetch()['total'];

    echo json_encode([
        'sucesso'     => true,
        'mensagem'    => 'Produto adicionado ao carrinho!',
        'total_itens' => $totalItens,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro interno ao adicionar ao carrinho.']);
}
