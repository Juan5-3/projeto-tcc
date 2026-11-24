<?php
// ============================================================
// ShopBR — API: Login de usuário
// POST /api/auth/login.php
// Content-Type: application/json
// Body: { "email": "", "senha": "" }
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

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Corpo da requisição inválido.']);
    exit;
}

$email = trim($input['email'] ?? '');
$senha = $input['senha']      ?? '';

// Validação básica
$erros = [];

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erros['email'] = 'Informe um e-mail válido.';
}

if ($senha === '') {
    $erros['senha'] = 'A senha é obrigatória.';
}

if (!empty($erros)) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Dados inválidos.', 'erros' => $erros]);
    exit;
}

try {
    $db = getDB();

    $stmt = $db->prepare('SELECT id, nome, email, senha_hash, tipo FROM usuarios WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    // Verifica credenciais — mensagem genérica para não expor qual campo está errado
    if (!$usuario || !password_verify($senha, $usuario['senha_hash'])) {
        http_response_code(401);
        echo json_encode(['sucesso' => false, 'mensagem' => 'E-mail ou senha inválidos.']);
        exit;
    }

    // Atualiza hash se o custo mudou (rehash automático)
    if (password_needs_rehash($usuario['senha_hash'], PASSWORD_BCRYPT, ['cost' => 12])) {
        $novoHash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);
        $db->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?')
           ->execute([$novoHash, $usuario['id']]);
    }

    logarUsuario(
        (int) $usuario['id'],
        $usuario['nome'],
        $usuario['email'],
        $usuario['tipo']
    );

    // Monta resposta sem expor o hash
    $resposta = [
        'sucesso'  => true,
        'mensagem' => 'Login realizado com sucesso!',
        'usuario'  => [
            'id'    => (int) $usuario['id'],
            'nome'  => $usuario['nome'],
            'email' => $usuario['email'],
            'tipo'  => $usuario['tipo'],
        ],
        // Redireciona conforme o tipo de conta
        'redirect' => $usuario['tipo'] === 'vendedor' ? 'dashboard-vendedor.php' : 'index.php',
    ];

    echo json_encode($resposta);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro interno. Tente novamente.']);
}
