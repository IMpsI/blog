<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$excludedDirectories = ['.git', 'node_modules', 'test-results', 'playwright-report'];
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$errors = [];

foreach ($files as $file) {
    if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
        continue;
    }

    $relativePath = substr($file->getPathname(), strlen($root) + 1);
    if (array_intersect(explode(DIRECTORY_SEPARATOR, $relativePath), $excludedDirectories)) {
        continue;
    }

    $output = [];
    $exitCode = 0;
    exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file->getPathname()), $output, $exitCode);
    if ($exitCode !== 0) {
        $errors[] = "{$relativePath}: " . implode(' ', $output);
    }
}

if ($errors !== []) {
    throw new RuntimeException("Erros de sintaxe encontrados:\n- " . implode("\n- ", $errors));
}

echo "Teste de sintaxe PHP passou.\n";
