CREATE DATABASE IF NOT EXISTS meublog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE meublog;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    usuario VARCHAR(80) NOT NULL UNIQUE,
    email VARCHAR(190) NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nivel ENUM('leitor', 'autor', 'admin') NOT NULL DEFAULT 'leitor',
    nome_artistico VARCHAR(150) NULL,
    email_confirmado TINYINT(1) NOT NULL DEFAULT 0,
    token_autenticacao CHAR(32) NULL,
    status_solicitacao VARCHAR(30) NOT NULL DEFAULT 'nenhuma',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    conteudo MEDIUMTEXT NOT NULL,
    autor_login VARCHAR(80) NOT NULL,
    data_publicacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME NULL,
    INDEX idx_posts_data (data_publicacao),
    CONSTRAINT fk_posts_autor FOREIGN KEY (autor_login) REFERENCES usuarios(usuario) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS comentarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id INT UNSIGNED NOT NULL,
    usuario_login VARCHAR(80) NOT NULL,
    comentario TEXT NOT NULL,
    editado TINYINT(1) NOT NULL DEFAULT 0,
    data_comentario DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_comentarios_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    CONSTRAINT fk_comentarios_usuario FOREIGN KEY (usuario_login) REFERENCES usuarios(usuario) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reacoes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id INT UNSIGNED NOT NULL,
    usuario_login VARCHAR(80) NOT NULL,
    tipo_reacao ENUM('like', 'dislike') NOT NULL,
    UNIQUE KEY uq_reacao (post_id, usuario_login),
    CONSTRAINT fk_reacoes_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    CONSTRAINT fk_reacoes_usuario FOREIGN KEY (usuario_login) REFERENCES usuarios(usuario) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;
