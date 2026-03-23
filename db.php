<?php
if (!class_exists('SQLite3')) {
    http_response_code(500);
    echo '<h1>Erro de configuracao do servidor</h1>';
    echo '<p>A extensao SQLite3 do PHP nao esta habilitada.</p>';
    echo '<p>No XAMPP, abra o php.ini e habilite as linhas:</p>';
    echo '<pre>extension=sqlite3
extension=pdo_sqlite</pre>';
    echo '<p>Depois reinicie o Apache no painel do XAMPP.</p>';
    exit;
}

$db = new SQLite3(__DIR__ . '/meublog.sqlite');

$db->exec("CREATE TABLE IF NOT EXISTS posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    titulo TEXT,
    conteudo TEXT,
    data_publicacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME,
    autor_login TEXT
)");

$db->exec("CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT,
    usuario TEXT UNIQUE,
    senha TEXT,
    nivel TEXT DEFAULT 'autor',
    nome_artistico TEXT
)");

$db->exec("CREATE TABLE IF NOT EXISTS comentarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_id INTEGER,
    usuario_login TEXT,
    comentario TEXT,
    data_comentario DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$db->exec("CREATE TABLE IF NOT EXISTS reacoes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_id INTEGER,
    usuario_login TEXT,
    tipo_reacao TEXT,
    UNIQUE(post_id, usuario_login)
)");

// Migrações compatíveis com base já existente.
@$db->exec("ALTER TABLE usuarios ADD COLUMN email TEXT");
@$db->exec("ALTER TABLE usuarios ADD COLUMN email_confirmado INTEGER DEFAULT 0");
@$db->exec("ALTER TABLE usuarios ADD COLUMN token_autenticacao TEXT");
@$db->exec("ALTER TABLE usuarios ADD COLUMN status_solicitacao TEXT DEFAULT 'nenhuma'");
@$db->exec("ALTER TABLE comentarios ADD COLUMN editado INTEGER DEFAULT 0");

$db->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_usuarios_email_unico
    ON usuarios(email)
    WHERE email IS NOT NULL");
