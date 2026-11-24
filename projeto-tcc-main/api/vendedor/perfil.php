<?php
// ============================================================
// ShopBR — API: Buscar perfil do vendedor logado
// GET /api/vendedor/perfil.php
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
if (!eVendedor()) {
    http_response_code(403);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Acesso restrito a vendedores.']);
    exit;
}

try {
    $db = getDB();

    $stmt = $db->prepare(
        'SELECT u.nome, u.email,
                v.id AS vendedor_id, v.nome_loja, v.cpf_cnpj, v.telefone, v.ativo
         FROM usuarios u
         INNER JOIN vendedores v ON v.usuario_id = u.id
         WHERE u.id = ? LIMIT 1'
    );
    $stmt->execute([$_SESSION['usuario_id']]);
    $perfil = $stmt->fetch();

    if (!$perfil) {
        http_response_code(404);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Perfil não encontrado.']);
        exit;
    }

    $perfil['vendedor_id'] = (int)  $perfil['vendedor_id'];
    $perfil['ativo']       = (bool) $perfil['ativo'];

    echo json_encode(['sucesso' => true, 'perfil' => $perfil]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro interno.']);
}
