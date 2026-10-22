<?php
declare(strict_types=1);

$css = file_get_contents(__DIR__ . '/../src/css/app.css');
$javascript = file_get_contents(__DIR__ . '/../src/js/app.js');
$header = file_get_contents(__DIR__ . '/../layout/header.php');
if ($css === false || $javascript === false || $header === false) {
    throw new RuntimeException('Arquivos frontend não encontrados.');
}

foreach ([
    '.dark-mode',
    ':focus-visible',
    'prefers-reduced-motion',
    '@media (max-width: 800px)',
    '.button:hover',
    '.brand:hover',
    '@keyframes text-haze',
    '@keyframes toast-fade',
    'text-shadow',
    '.main-nav a:hover',
    '.dashboard-grid a:hover',
    'table a:hover',
    '.site-footer p:hover',
    '.search-form:hover input',
    '.search-form:focus-within input',
    '.search-form:hover button',
    '.search-form:hover .search-results',
    'grid-template-columns: minmax(240px, 0.72fr) minmax(0, 1.8fr)',
    '.dashboard-grid table',
    '@media (max-width: 800px)',
] as $required) {
    if (!str_contains($css, $required)) {
        throw new RuntimeException("CSS sem proteção/interação: {$required}");
    }
}

if (!str_contains($css, 'prefers-reduced-motion')) {
    throw new RuntimeException('Interações de texto precisam respeitar redução de movimento.');
}

if (preg_match('/a:hover\s*\{[^}]*color:\s*var\(--accent-ink\)/s', $css)) {
    throw new RuntimeException('Links não podem usar a cor de texto dos botões no hover.');
}

foreach (['localStorage', 'textContent', 'replaceChildren', 'response.ok'] as $required) {
    if (!str_contains($javascript, $required)) {
        throw new RuntimeException("JavaScript sem requisito: {$required}");
    }
}

foreach (['toast', 'mouseenter', 'mouseleave', 'is-hidden'] as $required) {
    if (!str_contains($javascript, $required)) {
        throw new RuntimeException("Mensagens temporárias sem comportamento: {$required}");
    }
}

$messagePages = ['login.php', 'register.php', 'profile.php'];
foreach ($messagePages as $page) {
    $source = file_get_contents(__DIR__ . '/../' . $page);
    if ($source === false) {
        throw new RuntimeException("Página de mensagem não encontrada: {$page}");
    }
    if (preg_match('/class="message(?![^"]*\btoast\b)[^"]*"/', $source)) {
        throw new RuntimeException("Mensagem em {$page} precisa usar o padrão toast.");
    }
}

foreach (['--custom-surface-light', '--custom-surface-dark', '--custom-secondary-readable-light', '--custom-secondary-readable-dark', '--custom-header-light', '--custom-header-dark', '--custom-header-text-light', '--custom-header-text-dark', '.custom-theme'] as $required) {
    if (!str_contains($css, $required)) {
        throw new RuntimeException("CSS sem suporte ao tema personalizado: {$required}");
    }
}

if (!preg_match('/<a\s+href="profile\.php">Meu perfil<\/a>/', $header)) {
    throw new RuntimeException('O link Meu perfil precisa seguir o padrão dos links do menu.');
}
if (preg_match('/<a\s+class="[^"]*\bbutton\b[^"]*"[^>]*href="profile\.php"/', $header)) {
    throw new RuntimeException('O link Meu perfil não pode receber a aparência de botão.');
}
if (preg_match('/<nav\s+class="main-nav">.*<a\s+class="[^"]*\bbutton\b[^"]*"[^>]*>/s', $header)) {
    throw new RuntimeException('Links de navegação do cabeçalho não podem receber aparência de botão.');
}
foreach (['.auth-page', '.auth-box', '.auth-box .form-grid', 'text-align: center'] as $required) {
    if (!str_contains($css, $required)) {
        throw new RuntimeException("Layout de autenticação sem alinhamento padronizado: {$required}");
    }
    if (!str_contains(file_get_contents(__DIR__ . '/../profile.php') ?: '', 'reset_theme')) {
        throw new RuntimeException('O perfil precisa oferecer restauração do tema padrão.');
    }
}

echo "Testes de frontend passaram.\n";
