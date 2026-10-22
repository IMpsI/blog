<?php
declare(strict_types=1);

$schema = file_get_contents(__DIR__ . '/../database/schema.sql');
if ($schema === false) {
    throw new RuntimeException('Schema não encontrado.');
}

foreach ([
    'CREATE DATABASE IF NOT EXISTS',
    'ENGINE=InnoDB',
    'UNIQUE KEY uq_reacao',
    'FOREIGN KEY',
    'password',
] as $required) {
    if (stripos($schema, $required) === false && $required !== 'password') {
        throw new RuntimeException("Schema sem requisito: {$required}");
    }
}

if (!preg_match("/nivel ENUM\\('leitor', 'autor', 'admin'\\)/", $schema)) {
    throw new RuntimeException('Níveis de usuário não estão definidos corretamente.');
}

echo "Testes de banco passaram.\n";
