<?php
// ============================================================
// ShopBR — API: Criar oferta
// POST /api/oferta/criar.php
// Requer: sessão de vendedor
// Body: { "produto_id": 1, "desconto_pct": 20.00, "data_fim": "2026-12-31 23:59:59" }
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

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Corpo inválido.']);
    exit;
}

$produtoId   = filter_var($input['produto_id']   ?? null, FILTER_VALIDATE_INT);
$descontoPct = filter_var($input['desconto_pct'] ?? null, FILTER_VALIDATE_FLOAT);
$dataFim     = trim($input['data_fim'] ?? '');

$erros = [];
if (!$produtoId || $produtoId <= 0)             $erros['produto_id']   = 'Produto inválido.';
if ($descontoPct === false || $descontoPct <= 0
    || $descontoPct >= 100)                     $erros['desconto_pct'] = 'Desconto deve ser entre 1 e 99%.';
if ($dataFim === '')                            $erros['data_fim']     = 'Data de término é obrigatória.';
elseif (strtotime($dataFim) <= time())          $erros['data_fim']     = 'A data de término deve ser no futuro.';

if (!empty($erros)) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Dados inválidos.', 'erros' => $erros]);
    exit;
}

try {
    $db = getDB();

    // Confirma que o produto pertence ao vendedor logado
    $stmtV = $db->prepare('SELECT id FROM vendedores WHERE usuario_id = ? LIMIT 1');
    $stmtV->execute([$_SESSION['usuario_id']]);
    $vendedor = $stmtV->fetch();
    if (!$vendedor) {
        http_response_code(403);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Perfil de vendedor não encontrado.']);
        exit;
    }

    $stmtP = $db->prepare('SELECT id, preco FROM produtos WHERE id = ? AND vendedor_id = ? AND ativo = 1 LIMIT 1');
    $stmtP->execute([$produtoId, $vendedor['id']]);
    $produto = $stmtP->fetch();
    if (!$produto) {
        http_response_code(404);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Produto não encontrado ou não pertence a você.']);
        exit;
    }

    $precoOriginal = (float) $produto['preco'];
    $precoOferta   = round($precoOriginal * (1 - $descontoPct / 100), 2);
    $dataFimFmt    = date('Y-m-d H:i:s', strtotime($dataFim));

    // Desativa ofertas anteriores do mesmo produto
    $db->prepare('UPDATE ofertas SET ativa = 0 WHERE produto_id = ?')->execute([$produtoId]);

    $stmt = $db->prepare(
        'INSERT INTO ofertas (produto_id, desconto_pct, preco_oferta, data_fim)
         VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$produtoId, $descontoPct, $precoOferta, $dataFimFmt]);

    http_response_code(201);
    echo json_encode([
        'sucesso'      => true,
        'mensagem'     => 'Oferta criada com sucesso!',
        'preco_oferta' => $precoOferta,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro interno ao criar oferta.']);
}
