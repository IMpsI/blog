<?php
declare(strict_types=1);

$tests = [
    'contrast_test.php',
    'security_test.php',
    'database_test.php',
    'frontend_test.php',
    'routes_test.php',
];

foreach ($tests as $test) {
    require __DIR__ . '/' . $test;
}

echo "Todos os testes do projeto passaram.\n";
