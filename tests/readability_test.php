<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$extensions = ['php', 'js', 'css', 'sql', 'json', 'md', 'html'];
$excludedDirectories = ['.git', 'node_modules', 'test-results', 'playwright-report'];
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$violations = [];

foreach ($files as $file) {
    if (!$file->isFile() || !in_array(strtolower($file->getExtension()), $extensions, true)) {
        continue;
    }

    $relativePath = substr($file->getPathname(), strlen($root) + 1);
    $pathParts = explode(DIRECTORY_SEPARATOR, $relativePath);
    if (array_intersect($pathParts, $excludedDirectories)) {
        continue;
    }

    $content = file_get_contents($file->getPathname());
    if ($content === false) {
        throw new RuntimeException("Não foi possível ler {$relativePath}.");
    }
    if (preg_match('//u', $content) !== 1) {
        $violations[] = "{$relativePath}: texto não está em UTF-8 válido";
    }
    if (str_contains($content, "\r\n") && preg_match('/(?<!\r)\n/', $content)) {
        $violations[] = "{$relativePath}: mistura finais de linha Windows e Unix";
    }
    if (preg_match('/[^\S\r\n]+(?:\r?\n|$)/', $content)) {
        $violations[] = "{$relativePath}: espaços no fim de linha";
    }

    foreach (preg_split('/\r?\n/', $content) as $lineNumber => $line) {
        if (strlen($line) > 300) {
            $violations[] = sprintf(
                '%s:%d: linha com %d caracteres (limite humano: 300)',
                $relativePath,
                $lineNumber + 1,
                strlen($line)
            );
        }
        if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $line)) {
            $violations[] = sprintf('%s:%d: caractere de controle ilegível', $relativePath, $lineNumber + 1);
        }
    }
}

if ($violations !== []) {
    throw new RuntimeException("Problemas de legibilidade encontrados:\n- " . implode("\n- ", $violations));
}

echo "Teste de legibilidade textual passou.\n";
