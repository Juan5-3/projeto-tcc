<?php
// ============================================================
// ShopBR — API: Editar perfil do vendedor
// POST /api/vendedor/editar.php
// Body: { "nome": "", "nome_loja": "", "telefone": "",
//         "senha_atual": "", "nova_senha": "", "confirmar_nova": "" }
// Campos de senha são opcionais — só atualiza se senha_atual for informada.
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

$nome         = trim($input['nome']      ?? '');
$nomeLoja     = trim($input['nome_loja'] ?? '');
$telefone     = preg_replace('/\D/', '', $input['telefone'] ?? '');
$senhaAtual   = $input['senha_atual']    ?? '';
$novaSenha    = $input['nova_senha']     ?? '';
$confirmarNova = $input['confirmar_nova'] ?? '';

$erros = [];
if ($nome === '')     $erros['nome']      = 'O nome é obrigatório.';
if ($nomeLoja === '') $erros['nome_loja'] = 'O nome da loja é obrigatório.';
if ($telefone === '' || !in_array(strlen($telefone), [10, 11], true))
    $erros['telefone'] = 'Telefone inválido.';

$trocarSenha = $senhaAtual !== '';
if ($trocarSenha) {
    if ($novaSenha === '')               $erros['nova_senha']     = 'Informe a nova senha.';
    elseif (mb_strlen($novaSenha) < 8)  $erros['nova_senha']     = 'Mínimo 8 caracteres.';
    if ($novaSenha !== $confirmarNova)   $erros['confirmar_nova'] = 'As senhas não coincidem.';
}

if (!empty($erros)) {
    http_response_code(422);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Dados inválidos.', 'erros' => $erros]);
    exit;
}

try {
    $db        = getDB();
    $usuarioId = (int) $_SESSION['usuario_id'];

    // Verifica senha atual se o usuário quer trocar
    if ($trocarSenha) {
        $stmtHash = $db->prepare('SELECT senha_hash FROM usuarios WHERE id = ? LIMIT 1');
        $stmtHash->execute([$usuarioId]);
        $row = $stmtHash->fetch();

        if (!$row || !password_verify($senhaAtual, $row['senha_hash'])) {
            http_response_code(401);
            echo json_encode(['sucesso' => false, 'mensagem' => 'Senha atual incorreta.', 'erros' => ['senha_atual' => 'Senha incorreta.']]);
            exit;
        }
    }

    $db->beginTransaction();

    // Atualiza nome do usuário
    $db->prepare('UPDATE usuarios SET nome = ? WHERE id = ?')->execute([$nome, $usuarioId]);

    // Atualiza senha se solicitado
    if ($trocarSenha) {
        $hash = password_hash($novaSenha, PASSWORD_BCRYPT, ['cost' => 12]);
        $db->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?')->execute([$hash, $usuarioId]);
    }

    // Atualiza dados do vendedor
    $db->prepare('UPDATE vendedores SET nome_loja = ?, telefone = ? WHERE usuario_id = ?')
       ->execute([$nomeLoja, $telefone, $usuarioId]);

    $db->commit();

    // Atualiza sessão com o novo nome
    $_SESSION['usuario_nome'] = $nome;

    echo json_encode(['sucesso' => true, 'mensagem' => 'Perfil atualizado com sucesso!']);
} catch (PDOException $e) {
    if ($db->inTransaction()) $db->rollBack();
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro interno ao atualizar perfil.']);
}
