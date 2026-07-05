<?php
// ============================================================
// ShopBR — API: Registro de usuário comum (comprador)
// POST /api/auth/register.php
// Content-Type: application/json
// Body: { "nome": "", "email": "", "senha": "", "confirmar_senha": "" }
// ============================================================

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';

// --- Lê o corpo da requisição ---
$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Corpo da requisição inválido.']);
    exit;
}

// --- Sanitiza entradas ---
$nome           = trim($input['nome']           ?? '');
$email          = trim($input['email']          ?? '');
$senha          = $input['senha']               ?? '';
$confirmarSenha = $input['confirmar_senha']     ?? '';

// --- Validações ---
$erros = [];

if ($nome === '') {
    $erros['nome'] = 'O nome é obrigatório.';
} elseif (mb_strlen($nome) > 100) {
    $erros['nome'] = 'O nome deve ter no máximo 100 caracteres.';
}

if ($email === '') {
    $erros['email'] = 'O e-mail é obrigatório.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erros['email'] = 'Informe um e-mail válido.';
} elseif (mb_strlen($email) > 254) {
    $erros['email'] = 'E-mail muito longo.';
}

if ($senha === '') {
    $erros['senha'] = 'A senha é obrigatória.';
} elseif (mb_strlen($senha) < 8) {
    $erros['senha'] = 'A senha deve ter no mínimo 8 caracteres.';
}

if ($confirmarSenha !== $senha) {
    $erros['confirmar_senha'] = 'As senhas não coincidem.';
}

if (!empty($erros)) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Dados inválidos.', 'erros' => $erros]);
    exit;
}

// --- Persiste no banco ---
try {
    $db = getDB();

    // Verifica duplicidade de e-mail
    $stmt = $db->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Este e-mail já está cadastrado.', 'erros' => ['email' => 'E-mail já cadastrado.']]);
        exit;
    }

    // Insere o usuário
    $senhaHash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);

    $insert = $db->prepare(
        'INSERT INTO usuarios (nome, email, senha_hash, tipo) VALUES (?, ?, ?, ?)'
    );
    $insert->execute([$nome, $email, $senhaHash, 'comprador']);

    $userId = (int) $db->lastInsertId();

    // Loga automaticamente após o cadastro
    logarUsuario($userId, $nome, $email, 'comprador');

    http_response_code(201);
    echo json_encode([
        'sucesso'  => true,
        'mensagem' => 'Conta criada com sucesso!',
        'usuario'  => ['id' => $userId, 'nome' => $nome, 'email' => $email, 'tipo' => 'comprador'],
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro interno ao criar a conta. Tente novamente.']);
    // Em produção, registre o erro: error_log($e->getMessage());
}
