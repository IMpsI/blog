<?php
require_once __DIR__ . '/config.php';

try {
    $server = new PDO(
        sprintf('mysql:host=%s;port=%s;charset=utf8mb4', envValue('BLOG_DB_HOST', DB_HOST), envValue('BLOG_DB_PORT', DB_PORT)),
        envValue('BLOG_DB_USER', DB_USER),
        envValue('BLOG_DB_PASS', DB_PASS),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $schema = file_get_contents(__DIR__ . '/database/schema.sql');
    foreach (array_filter(array_map('trim', preg_split('/;\s*(?:\r?\n|$)/', $schema))) as $statement) {
        $server->exec($statement);
    }
    echo 'Banco MySQL criado/atualizado com sucesso.';
} catch (PDOException $exception) {
    http_response_code(500);
    echo 'Falha ao configurar o banco: ' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8');
}
