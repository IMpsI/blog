<?php
declare(strict_types=1);

$css = file_get_contents(__DIR__ . '/../src/css/app.css');
$javascript = file_get_contents(__DIR__ . '/../src/js/app.js');
if ($css === false || $javascript === false) {
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

echo "Testes de frontend passaram.\n";
