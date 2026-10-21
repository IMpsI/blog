<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function db(): PDO
{
    static $connection;
    if ($connection instanceof PDO) {
        return $connection;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        envValue('BLOG_DB_HOST', DB_HOST),
        envValue('BLOG_DB_PORT', DB_PORT),
        envValue('BLOG_DB_NAME', DB_NAME)
    );

    try {
        $connection = new PDO($dsn, envValue('BLOG_DB_USER', DB_USER), envValue('BLOG_DB_PASS', DB_PASS), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $connection;
    } catch (PDOException $exception) {
        http_response_code(500);
        exit('<h1>Não foi possível conectar ao MySQL.</h1><p>Confira as variáveis BLOG_DB_* e se o banco existe.</p>');
    }
}

function dbAll(string $sql, array $params = []): array
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    return $statement->fetchAll();
}

function dbOne(string $sql, array $params = []): ?array
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    $row = $statement->fetch();
    return $row ?: null;
}

function dbExec(string $sql, array $params = []): int
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    return $statement->rowCount();
}
