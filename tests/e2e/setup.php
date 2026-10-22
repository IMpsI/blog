<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';

$server = new PDO(
    sprintf('mysql:host=%s;port=%s;charset=utf8mb4', DB_HOST, DB_PORT),
    DB_USER,
    DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$server->exec('DROP DATABASE IF EXISTS meublog_test');
$server->exec('CREATE DATABASE meublog_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$schema = file_get_contents(__DIR__ . '/../../database/schema.sql');
if ($schema === false) {
    throw new RuntimeException('Schema não encontrado.');
}
$testDb = new PDO(
    sprintf('mysql:host=%s;port=%s;dbname=meublog_test;charset=utf8mb4', DB_HOST, DB_PORT),
    DB_USER,
    DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
foreach (array_filter(array_map('trim', preg_split('/;\s*(?:\r?\n|$)/', $schema))) as $statement) {
    if (!preg_match('/^(CREATE DATABASE|USE )/i', $statement)) {
        $testDb->exec($statement);
    }
}

$passwords = [
    'teste_leitor' => 'Leitor123!',
    'teste_autor' => 'Autor123!',
    'teste_outro' => 'Outro123!',
    'teste_admin' => 'Admin123!',
    'teste_pendente' => 'Leitor123!',
];
$users = [
    ['Leitor de Teste', 'teste_leitor', 'leitor'],
    ['Autor de Teste', 'teste_autor', 'autor'],
    ['Outro Autor', 'teste_outro', 'autor'],
    ['Admin de Teste', 'teste_admin', 'admin'],
    ['Conta Pendente', 'teste_pendente', 'leitor'],
];
$insertUser = $testDb->prepare(
    'INSERT INTO usuarios (nome, usuario, email, senha, nivel, email_confirmado)
     VALUES (?, ?, ?, ?, ?, 1)'
);
foreach ($users as [$name, $username, $level]) {
    $insertUser->execute([$name, $username, "{$username}@example.test", password_hash($passwords[$username], PASSWORD_DEFAULT), $level]);
}
$testDb->prepare('UPDATE usuarios SET email_confirmado = 0, token_autenticacao = ? WHERE usuario = ?')
    ->execute(['0123456789abcdef0123456789abcdef', 'teste_pendente']);
$insertPost = $testDb->prepare(
    'INSERT INTO posts (titulo, conteudo, autor_login) VALUES (?, ?, ?)'
);
$insertPost->execute(['História de teste', 'Conteúdo público para os testes.', 'teste_autor']);
$insertPost->execute(['História do outro autor', 'Outro conteúdo público.', 'teste_outro']);

echo "Banco de testes preparado.\n";
