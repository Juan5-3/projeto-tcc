<?php
// ============================================================
// ShopBR — Helpers de sessão e autenticação
// ============================================================

/**
 * Inicia a sessão com configurações seguras, se ainda não iniciada.
 */
function iniciarSessao(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,               // encerra ao fechar o browser
            'path'     => '/',
            'secure'   => false,           // true em produção (HTTPS)
            'httponly' => true,            // bloqueia acesso via JS
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

/**
 * Salva os dados do usuário autenticado na sessão.
 */
function logarUsuario(int $id, string $nome, string $email, string $tipo): void
{
    iniciarSessao();
    session_regenerate_id(true); // previne session fixation
    $_SESSION['usuario_id']    = $id;
    $_SESSION['usuario_nome']  = $nome;
    $_SESSION['usuario_email'] = $email;
    $_SESSION['usuario_tipo']  = $tipo;
}

/**
 * Retorna true se houver um usuário autenticado na sessão.
 */
function estaLogado(): bool
{
    iniciarSessao();
    return !empty($_SESSION['usuario_id']);
}

/**
 * Retorna true se o usuário logado for do tipo 'vendedor'.
 */
function eVendedor(): bool
{
    return estaLogado() && ($_SESSION['usuario_tipo'] ?? '') === 'vendedor';
}

/**
 * Retorna o array com os dados do usuário logado ou null.
 */
function usuarioLogado(): ?array
{
    if (!estaLogado()) {
        return null;
    }
    return [
        'id'    => $_SESSION['usuario_id'],
        'nome'  => $_SESSION['usuario_nome'],
        'email' => $_SESSION['usuario_email'],
        'tipo'  => $_SESSION['usuario_tipo'],
    ];
}

/**
 * Destrói a sessão completamente.
 */
function deslogar(): void
{
    iniciarSessao();
    $_SESSION = [];
    session_destroy();
}

/**
 * Redireciona para login se o usuário não estiver autenticado.
 * Uso: exigirLogin() no topo de páginas protegidas.
 */
function exigirLogin(string $redirect = '/login.php'): void
{
    if (!estaLogado()) {
        header('Location: ' . $redirect);
        exit;
    }
}

/**
 * Redireciona se o usuário não for vendedor.
 */
function exigirVendedor(string $redirect = '/login.php'): void
{
    if (!eVendedor()) {
        header('Location: ' . $redirect);
        exit;
    }
}