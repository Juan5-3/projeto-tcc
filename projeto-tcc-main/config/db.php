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

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $serverDsn = sprintf(
        'mysql:host=%s;port=%s;charset=%s',
        DB_HOST,
        DB_PORT,
        DB_CHARSET
    );

    $server = new PDO($serverDsn, DB_USER, DB_PASS, $options);
    $lock = $server->prepare('SELECT GET_LOCK(?, 10)');
    $lock->execute(['schema-init:' . DB_NAME]);

    if ((int) $lock->fetchColumn() !== 1) {
        throw new RuntimeException('Não foi possível obter o bloqueio para inicializar o banco de dados.');
    }

    try {
        $databaseExists = $server->prepare(
            'SELECT 1 FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?'
        );
        $databaseExists->execute([DB_NAME]);
        $databaseMissing = $databaseExists->fetchColumn() === false;

        if ($databaseMissing) {
            $databaseName = '`' . str_replace('`', '``', DB_NAME) . '`';
            $server->exec(
                'CREATE DATABASE IF NOT EXISTS ' . $databaseName
                . ' CHARACTER SET ' . DB_CHARSET
                . ' COLLATE utf8mb4_unicode_ci'
            );
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );
        $connection = new PDO($dsn, DB_USER, DB_PASS, $options);

        $tables = $connection->query(
            'SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE()'
        )->fetchAll(PDO::FETCH_COLUMN);
        $requiredTables = [
            'usuarios',
            'vendedores',
            'categorias',
            'produtos',
            'ofertas',
            'carrinho_itens',
            'pedidos',
            'pedido_itens',
        ];
        $missingTables = array_diff($requiredTables, $tables);
        $visualizationsColumnExists = $connection->query(
            "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'produtos'
               AND COLUMN_NAME = 'visualizacoes'"
        )->fetchColumn() !== false;

        if ($databaseMissing || $missingTables !== [] || !$visualizationsColumnExists) {
            initializeDatabaseSchema($connection);
        }
    } finally {
        $releaseLock = $server->prepare('SELECT RELEASE_LOCK(?)');
        $releaseLock->execute(['schema-init:' . DB_NAME]);
    }

    $pdo = $connection;

    return $pdo;
}

/**
 * Executa o script do projeto no banco já selecionado pela conexão.
 */
function initializeDatabaseSchema(PDO $pdo): void
{
    $schemaPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database.sql';
    $schema = file_get_contents($schemaPath);

    if ($schema === false) {
        throw new RuntimeException('Não foi possível ler o arquivo database.sql.');
    }

    $schema = preg_replace('/^\s*CREATE\s+DATABASE\b.*?;\s*/ims', '', $schema, 1, $databaseStatementsRemoved);
    $schema = preg_replace('/^\s*USE\s+[`"]?[^`"\s;]+[`"]?\s*;\s*/im', '', $schema, 1, $useStatementsRemoved);

    if ($databaseStatementsRemoved !== 1 || $useStatementsRemoved !== 1) {
        throw new RuntimeException('O arquivo database.sql não contém as instruções esperadas.');
    }

    $statements = preg_split('/;\s*(?:--[^\r\n]*)?(?:\r?\n|$)/', $schema);

    if ($statements === false) {
        throw new RuntimeException('Não foi possível processar o arquivo database.sql.');
    }

    foreach ($statements as $statement) {
        if (trim($statement) !== '') {
            $pdo->exec($statement);
        }
    }
}
