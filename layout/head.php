<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? APP_NAME) ?></title>
    <link rel="stylesheet" href="src/css/app.css?v=<?= (int) filemtime(__DIR__ . '/../src/css/app.css') ?>-<?= (int) filesize(__DIR__ . '/../src/css/app.css') ?>">
    <?php foreach (($styles ?? []) as $style): ?>
        <link rel="stylesheet" href="src/css/<?= e($style) ?>">
    <?php endforeach; ?>
</head>
<body<?= $bodyTheme ?? '' ?>>
