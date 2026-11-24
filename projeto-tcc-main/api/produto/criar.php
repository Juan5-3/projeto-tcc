<?php
// ============================================================
// ShopBR — API: Criar produto
// POST /api/produto/criar.php
// Requer: usuário logado como vendedor (sessão ativa)
// Content-Type: application/json
// Body: {
//   "nome": "", "descricao": "", "preco": 0.00,
//   "estoque": 0, "categoria_id": 1, "imagem_url": ""
// }
// ============================================================

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

// --- Autenticação ---
iniciarSessao();

if (!estaLogado()) {
    http_response_code(401);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Você precisa estar logado.']);
    exit;
}

if (!eVendedor()) {
    http_response_code(403);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Apenas vendedores podem cadastrar produtos.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Corpo da requisição inválido.']);
    exit;
}

// --- Sanitiza ---
$nome        = trim($input['nome']       ?? '');
$descricao   = trim($input['descricao']  ?? '');
$preco       = filter_var($input['preco']   ?? null, FILTER_VALIDATE_FLOAT);
$estoque     = filter_var($input['estoque'] ?? null, FILTER_VALIDATE_INT);
$categoriaId = filter_var($input['categoria_id'] ?? null, FILTER_VALIDATE_INT);
$imagemUrl   = trim($input['imagem_url'] ?? '');

// --- Validações ---
$erros = [];

if ($nome === '') {
    $erros['nome'] = 'O nome do produto é obrigatório.';
} elseif (mb_strlen($nome) > 150) {
    $erros['nome'] = 'O nome deve ter no máximo 150 caracteres.';
}

if ($preco === false || $preco === null || $preco < 0) {
    $erros['preco'] = 'Informe um preço válido (maior ou igual a zero).';
}

if ($estoque === false || $estoque === null || $estoque < 0) {
    $erros['estoque'] = 'O estoque deve ser um número inteiro maior ou igual a zero.';
}

if ($categoriaId === false || $categoriaId === null || $categoriaId <= 0) {
    $erros['categoria_id'] = 'Selecione uma categoria válida.';
}

if ($imagemUrl !== '' && !filter_var($imagemUrl, FILTER_VALIDATE_URL)) {
    $erros['imagem_url'] = 'Informe uma URL de imagem válida.';
}

if (!empty($erros)) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Dados inválidos.', 'erros' => $erros]);
    exit;
}

try {
    $db = getDB();

    // Busca o vendedor_id pelo usuario_id na sessão
    $stmtV = $db->prepare('SELECT id FROM vendedores WHERE usuario_id = ? LIMIT 1');
    $stmtV->execute([$_SESSION['usuario_id']]);
    $vendedor = $stmtV->fetch();

    if (!$vendedor) {
        http_response_code(403);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Perfil de vendedor não encontrado.']);
        exit;
    }

    // Verifica se a categoria existe
    $stmtC = $db->prepare('SELECT id FROM categorias WHERE id = ? LIMIT 1');
    $stmtC->execute([$categoriaId]);
    if (!$stmtC->fetch()) {
        http_response_code(422);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Categoria não encontrada.', 'erros' => ['categoria_id' => 'Categoria inválida.']]);
        exit;
    }

    $stmt = $db->prepare(
        'INSERT INTO produtos (vendedor_id, categoria_id, nome, descricao, preco, estoque, imagem_url)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        (int) $vendedor['id'],
        (int) $categoriaId,
        $nome,
        $descricao ?: null,
        round((float) $preco, 2),
        (int) $estoque,
        $imagemUrl ?: null,
    ]);

    $produtoId = (int) $db->lastInsertId();

    http_response_code(201);
    echo json_encode([
        'sucesso'   => true,
        'mensagem'  => 'Produto cadastrado com sucesso!',
        'produto'   => [
            'id'           => $produtoId,
            'nome'         => $nome,
            'preco'        => round((float) $preco, 2),
            'estoque'      => (int) $estoque,
            'categoria_id' => (int) $categoriaId,
        ],
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro interno ao cadastrar o produto. Tente novamente.']);
}
