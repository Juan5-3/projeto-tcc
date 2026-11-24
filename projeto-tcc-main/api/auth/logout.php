<?php
// ============================================================
// ShopBR — API: Logout
// POST /api/auth/logout.php  (GET também aceito para links simples)
// ============================================================

declare(strict_types=1);

require_once __DIR__ . '/../../config/session.php';

deslogar();

// Se a requisição veio via fetch (AJAX), devolve JSON
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
    || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['sucesso' => true, 'mensagem' => 'Sessão encerrada.']);
} else {
    // Navegação direta — redireciona para a home
    header('Location: ../../index.php');
}

exit;
