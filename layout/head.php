<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php
    $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    if ($basePath === '' || $basePath === '.') {
        $basePath = '';
    }
    ?>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle ?? 'O Blog - Biblioteca Digital') ?></title>
    <link rel="stylesheet" href="<?= $basePath ?>/src/css/style.css">
    <link rel="stylesheet" href="<?= $basePath ?>/src/css/painel.css">
</head>
<body>