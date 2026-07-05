<?php
// ============================================================
// ShopBR — API: Cadastro de vendedor
// POST /api/vendedor/cadastrar.php
// Content-Type: application/json
// Body: {
//   "nome": "", "email": "", "senha": "", "confirmar_senha": "",
//   "nome_loja": "", "cpf_cnpj": "", "telefone": ""
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

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Corpo da requisição inválido.']);
    exit;
}

// --- Sanitiza ---
$nome           = trim($input['nome']           ?? '');
$email          = trim($input['email']          ?? '');
$senha          = $input['senha']               ?? '';
$confirmarSenha = $input['confirmar_senha']     ?? '';
$nomeLoja       = trim($input['nome_loja']      ?? '');
$cpfCnpj        = preg_replace('/\D/', '', $input['cpf_cnpj'] ?? '');  // só dígitos
$telefone       = preg_replace('/\D/', '', $input['telefone'] ?? '');  // só dígitos

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
}

if ($senha === '') {
    $erros['senha'] = 'A senha é obrigatória.';
} elseif (mb_strlen($senha) < 8) {
    $erros['senha'] = 'A senha deve ter no mínimo 8 caracteres.';
}

if ($confirmarSenha !== $senha) {
    $erros['confirmar_senha'] = 'As senhas não coincidem.';
}

if ($nomeLoja === '') {
    $erros['nome_loja'] = 'O nome da loja é obrigatório.';
} elseif (mb_strlen($nomeLoja) > 100) {
    $erros['nome_loja'] = 'O nome da loja deve ter no máximo 100 caracteres.';
}

// Valida CPF (11 dígitos) ou CNPJ (14 dígitos)
if ($cpfCnpj === '') {
    $erros['cpf_cnpj'] = 'CPF ou CNPJ é obrigatório.';
} elseif (!in_array(strlen($cpfCnpj), [11, 14], true)) {
    $erros['cpf_cnpj'] = 'CPF deve ter 11 dígitos e CNPJ 14 dígitos.';
}

// Valida telefone (10 fixo ou 11 celular)
if ($telefone === '') {
    $erros['telefone'] = 'O telefone é obrigatório.';
} elseif (!in_array(strlen($telefone), [10, 11], true)) {
    $erros['telefone'] = 'Telefone deve ter 10 ou 11 dígitos.';
}

if (!empty($erros)) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Dados inválidos.', 'erros' => $erros]);
    exit;
}

// --- Persiste em transação (usuarios + vendedores) ---
try {
    $db = getDB();

    // E-mail duplicado?
    $stmtEmail = $db->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
    $stmtEmail->execute([$email]);
    if ($stmtEmail->fetch()) {
        http_response_code(409);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Este e-mail já está cadastrado.', 'erros' => ['email' => 'E-mail já cadastrado.']]);
        exit;
    }

    // CPF/CNPJ duplicado?
    $stmtDoc = $db->prepare('SELECT id FROM vendedores WHERE cpf_cnpj = ? LIMIT 1');
    $stmtDoc->execute([$cpfCnpj]);
    if ($stmtDoc->fetch()) {
        http_response_code(409);
        echo json_encode(['sucesso' => false, 'mensagem' => 'CPF/CNPJ já cadastrado.', 'erros' => ['cpf_cnpj' => 'CPF/CNPJ já em uso.']]);
        exit;
    }

    $db->beginTransaction();

    $senhaHash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);

    // 1) Insere usuário como 'vendedor'
    $db->prepare(
        'INSERT INTO usuarios (nome, email, senha_hash, tipo) VALUES (?, ?, ?, ?)'
    )->execute([$nome, $email, $senhaHash, 'vendedor']);

    $usuarioId = (int) $db->lastInsertId();

    // 2) Insere dados do vendedor
    $db->prepare(
        'INSERT INTO vendedores (usuario_id, nome_loja, cpf_cnpj, telefone) VALUES (?, ?, ?, ?)'
    )->execute([$usuarioId, $nomeLoja, $cpfCnpj, $telefone]);

    $vendedorId = (int) $db->lastInsertId();

    $db->commit();

    // Loga automaticamente
    logarUsuario($usuarioId, $nome, $email, 'vendedor');

    http_response_code(201);
    echo json_encode([
        'sucesso'    => true,
        'mensagem'   => 'Cadastro de vendedor realizado com sucesso!',
        'vendedor'   => [
            'usuario_id'  => $usuarioId,
            'vendedor_id' => $vendedorId,
            'nome'        => $nome,
            'email'       => $email,
            'nome_loja'   => $nomeLoja,
        ],
        'redirect' => 'dashboard-vendedor.php',
    ]);

} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro interno ao realizar o cadastro. Tente novamente.']);
}
