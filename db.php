<?php
$db = new SQLite3('meublog.sqlite');

// 1. Cria a tabela de Posts (mantida igual)
$db->exec("CREATE TABLE IF NOT EXISTS posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    titulo TEXT,
    conteudo TEXT,
    data_publicacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME,
    autor_login TEXT
)");

// 2. Cria a tabela de Usuários (mantida igual na base)
$db->exec("CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT,
    usuario TEXT UNIQUE,
    senha TEXT,
    nivel TEXT DEFAULT 'autor',
    nome_artistico TEXT
)");

// --- EXPANSÃO DO BANCO DE DADOS ---

// Adiciona as novas colunas na tabela de usuários para o sistema de Leitores e E-mails
@$db->exec("ALTER TABLE usuarios ADD COLUMN email TEXT UNIQUE");
@$db->exec("ALTER TABLE usuarios ADD COLUMN email_confirmado INTEGER DEFAULT 0"); // 0 = Não, 1 = Sim
@$db->exec("ALTER TABLE usuarios ADD COLUMN token_autenticacao TEXT"); // Usado para validar email e recuperar senha
@$db->exec("ALTER TABLE usuarios ADD COLUMN status_solicitacao TEXT DEFAULT 'nenhuma'"); // 'nenhuma', 'pendente', 'rejeitada'

// Cria a tabela de Comentários
$db->exec("CREATE TABLE IF NOT EXISTS comentarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_id INTEGER,
    usuario_login TEXT,
    comentario TEXT,
    data_comentario DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// --- EXPANSÃO DO BANCO DE DADOS ---

// Adiciona as novas colunas (sem o UNIQUE, pois o SQLite não permite no ALTER TABLE)
@$db->exec("ALTER TABLE usuarios ADD COLUMN email TEXT");
@$db->exec("ALTER TABLE usuarios ADD COLUMN email_confirmado INTEGER DEFAULT 0");
@$db->exec("ALTER TABLE usuarios ADD COLUMN token_autenticacao TEXT");
@$db->exec("ALTER TABLE usuarios ADD COLUMN status_solicitacao TEXT DEFAULT 'nenhuma'");

// Cria a tabela de Comentários
$db->exec("CREATE TABLE IF NOT EXISTS comentarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_id INTEGER,
    usuario_login TEXT,
    comentario TEXT,
    data_comentario DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Cria a tabela de Reações (Curtir / Não Curtir)
$db->exec("CREATE TABLE IF NOT EXISTS reacoes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_id INTEGER,
    usuario_login TEXT,
    tipo_reacao TEXT, 
    UNIQUE(post_id, usuario_login)
)");
