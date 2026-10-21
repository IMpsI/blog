<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$sqliteFile = __DIR__ . '/../blog/meublog.sqlite';
if (!is_file($sqliteFile)) {
    exit("Arquivo SQLite não encontrado: {$sqliteFile}\n");
}

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

    $mysql = new PDO(
        sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', envValue('BLOG_DB_HOST', DB_HOST), envValue('BLOG_DB_PORT', DB_PORT), envValue('BLOG_DB_NAME', DB_NAME)),
        envValue('BLOG_DB_USER', DB_USER),
        envValue('BLOG_DB_PASS', DB_PASS),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $sqlite = new PDO('sqlite:' . $sqliteFile, null, null, [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $mysql->beginTransaction();

    foreach ([
        'usuarios' => 'INSERT IGNORE INTO usuarios (id,nome,usuario,email,senha,nivel,nome_artistico,email_confirmado,token_autenticacao,status_solicitacao) VALUES (:id,:nome,:usuario,:email,:senha,:nivel,:nome_artistico,:email_confirmado,:token_autenticacao,:status_solicitacao)',
        'posts' => 'INSERT IGNORE INTO posts (id,titulo,conteudo,data_publicacao,data_atualizacao,autor_login) VALUES (:id,:titulo,:conteudo,:data_publicacao,:data_atualizacao,:autor_login)',
        'comentarios' => 'INSERT IGNORE INTO comentarios (id,post_id,usuario_login,comentario,data_comentario,editado) VALUES (:id,:post_id,:usuario_login,:comentario,:data_comentario,:editado)',
        'reacoes' => 'INSERT IGNORE INTO reacoes (id,post_id,usuario_login,tipo_reacao) VALUES (:id,:post_id,:usuario_login,:tipo_reacao)',
    ] as $table => $insert) {
        $rows = $sqlite->query("SELECT * FROM {$table}")->fetchAll();
        foreach ($rows as $row) {
            $mysql->prepare($insert)->execute($row);
        }
        echo "{$table}: " . count($rows) . " registros\n";
    }
    $mysql->commit();
    echo "Migração concluída.\n";
} catch (PDOException $exception) {
    if (isset($mysql) && $mysql->inTransaction()) {
        $mysql->rollBack();
    }
    fwrite(STDERR, "Falha na migração: {$exception->getMessage()}\n");
    exit(1);
}
