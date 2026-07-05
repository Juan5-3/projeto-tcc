<?php
// ============================================================
// ShopBR — Conexão com o banco de dados via PDO
// ============================================================

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'shopbr');
define('DB_USER', 'root');
define('DB_PASS', '');          // padrão XAMPP: senha vazia
define('DB_CHARSET', 'utf8mb4');

/**
 * Retorna uma instância singleton da conexão PDO.
 * Lança uma exceção em caso de falha — o chamador decide como tratar.
 */
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    return $pdo;
}
