<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$pages = [
    'index.php',
    'read.php',
    'login.php',
    'register.php',
    'dashboard.php',
    'editor.php',
    'profile.php',
    'users.php',
    'reaction.php',
    'confirm_email.php',
];

foreach ($pages as $page) {
    if (!is_file($root . '/' . $page)) {
        throw new RuntimeException("Página ausente: {$page}");
    }
}

$reaction = file_get_contents($root . '/reaction.php');
if ($reaction === false || !str_contains($reaction, 'Content-Type: application/json')) {
    throw new RuntimeException('Endpoint de reação precisa responder JSON.');
}

$head = file_get_contents($root . '/layout/head.php');
if ($head === false || !str_contains($head, 'charset="UTF-8"')) {
    throw new RuntimeException('Páginas precisam declarar UTF-8.');
}

echo "Testes de rotas passaram.\n";
