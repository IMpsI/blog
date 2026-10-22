<?php
declare(strict_types=1);

$css = file_get_contents(__DIR__ . '/../src/css/app.css');
if ($css === false) {
    throw new RuntimeException('Não foi possível ler o CSS da aplicação.');
}

$head = file_get_contents(__DIR__ . '/../layout/head.php');
if ($head === false || !preg_match('/app\.css\?v=<\?=\s*\(int\)\s*filemtime\(/', $head)) {
    throw new RuntimeException('O stylesheet principal precisa usar versionamento para evitar cache antigo.');
}

function cssDeclarations(string $css, string $selector): string
{
    $selectors = array_map(
        static fn (string $part): string => preg_quote(trim($part), '/'),
        explode(',', $selector)
    );
    $selectorPattern = implode('\s*,\s*', $selectors);
    preg_match('/' . $selectorPattern . '\s*\{([^}]*)\}/', $css, $match);
    if (!$match) {
        throw new RuntimeException("Seletor {$selector} não encontrado.");
    }
    return $match[1];
}

function cssValue(string $declarations, string $property): string
{
    preg_match('/(?:^|;)\s*' . preg_quote($property, '/') . '\s*:\s*([^;}]*)/', $declarations, $match);
    return trim($match[1] ?? '');
}

function hexRgb(string $color): array
{
    if (!preg_match('/^#([0-9a-f]{6})$/i', $color, $match)) {
        throw new RuntimeException("Cor não suportada no teste: {$color}");
    }
    return [
        hexdec(substr($match[1], 0, 2)),
        hexdec(substr($match[1], 2, 2)),
        hexdec(substr($match[1], 4, 2)),
    ];
}

function contrastRatio(string $foreground, string $background): float
{
    $relativeLuminance = static function (array $rgb): float {
        $channels = array_map(static function (int $channel): float {
            $value = $channel / 255;
            return $value <= 0.03928 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        }, $rgb);
        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    };

    $foregroundLuminance = $relativeLuminance(hexRgb($foreground));
    $backgroundLuminance = $relativeLuminance(hexRgb($background));
    return (max($foregroundLuminance, $backgroundLuminance) + 0.05)
        / (min($foregroundLuminance, $backgroundLuminance) + 0.05);
}

function bestTextColor(string $background): string
{
    return contrastRatio('#FFFFFF', $background) >= contrastRatio('#111827', $background)
        ? '#FFFFFF'
        : '#111827';
}

function resolveCssToken(string $value, array $variables): string
{
    if (preg_match('/^var\((--[^)]+)\)$/', $value, $match)) {
        return $variables[$match[1]] ?? '';
    }
    return $value;
}

$root = cssDeclarations($css, ':root');
$dark = cssDeclarations($css, '.dark-mode');
$message = cssDeclarations($css, '.message');
$controls = cssDeclarations($css, '.button,.btn-act,.btn-submit,.btn-send,.nav-btn');
$inputs = cssDeclarations($css, '.form-grid input,.form-grid textarea,.form-grid select,.comment-form textarea,.edit-form textarea');

$themes = [
    'tema claro' => $root,
    'tema escuro' => $dark,
];
$components = [
    'mensagens' => [$message, 'var(--message-ink)', 'var(--message-bg)'],
    'controles' => [$controls, 'var(--control-ink)', 'var(--control-bg)'],
    'campos' => [$inputs, 'var(--ink)', 'var(--input-bg)'],
];

foreach ($themes as $theme => $tokens) {
    $variables = [];
    foreach ([
        '--paper',
        '--ink',
        '--muted',
        '--message-ink',
        '--message-bg',
        '--control-ink',
        '--control-bg',
        '--input-bg',
        '--accent',
        '--accent-ink',
        '--link-color',
        '--danger-bg',
        '--danger-ink',
        '--placeholder',
    ] as $variable) {
        $variables[$variable] = cssValue($tokens, $variable);
        if ($variables[$variable] === '') {
            throw new RuntimeException("Token {$variable} ausente no {$theme}.");
        }
    }

    $components['botões principais'] = ['', 'var(--accent-ink)', 'var(--accent)'];
    $components['botões de perigo'] = ['', 'var(--danger-ink)', 'var(--danger-bg)'];
    $components['placeholders'] = ['', 'var(--placeholder)', 'var(--input-bg)'];
    $components['links'] = ['', 'var(--accent)', 'var(--paper)'];
    $components['links'] = ['', 'var(--link-color)', 'var(--paper)'];
    $components['texto secundário'] = ['', 'var(--muted)', 'var(--paper)'];

    foreach ($components as $component => [$declarations, $foreground, $background]) {
        $foreground = resolveCssToken($foreground, $variables);
        $background = resolveCssToken($background, $variables);
        $ratio = contrastRatio($foreground, $background);
        if ($ratio < 4.5) {
            throw new RuntimeException(sprintf(
                'Contraste insuficiente em %s (%s): %.2f:1; mínimo 4.5:1.',
                $component,
                $theme,
                $ratio
            ));
        }
    }
}

echo "Teste de contraste do projeto passou (WCAG AA).\n";

$customThemeColors = [
    '#000000',
    '#123456',
    '#1769AA',
    '#6B7280',
    '#ABCDEF',
    '#FFFFFF',
    '#FDE68A',
    '#DC2626',
    '#16A34A',
    '#7C3AED',
];

foreach ($customThemeColors as $background) {
    $text = bestTextColor($background);
    $ratio = contrastRatio($text, $background);
    if ($ratio < 4.5) {
        throw new RuntimeException(sprintf(
            'Contraste insuficiente no tema personalizado (%s com %s): %.2f:1; mínimo 4.5:1.',
            $text,
            $background,
            $ratio
        ));
    }
}

if (!str_contains($css, '--paper: var(--custom-surface-light)')
    || !str_contains($css, '--paper: var(--custom-surface-dark)')
    || !str_contains($css, '--ink: var(--custom-secondary-readable-light)')
    || !str_contains($css, '--ink: var(--custom-secondary-readable-dark)')) {
    throw new RuntimeException('O tema personalizado precisa usar a cor principal nas superfícies e a secundária legível no conteúdo.');
}

if (!str_contains(file_get_contents(__DIR__ . '/../bootstrap.php') ?: '', 'themeComfortColor')
    || !str_contains($css, '--header-ink: var(--custom-header-text-light)')
    || !str_contains($css, '--header-ink: var(--custom-header-text-dark)')) {
    throw new RuntimeException('A paleta personalizada precisa limitar a saturação e separar texto de cabeçalho do conteúdo.');
}

$saturation = static function (string $color): float {
    [$red, $green, $blue] = array_map(
        static fn (string $channel): float => hexdec($channel) / 255,
        str_split(substr($color, 1), 2)
    );
    $maximum = max($red, $green, $blue);
    $minimum = min($red, $green, $blue);
    $lightness = ($maximum + $minimum) / 2;
    if ($maximum === $minimum) return 0.0;
    return ($maximum - $minimum) / (1 - abs(2 * $lightness - 1));
};

foreach (['#FF00FF', '#00FF00', '#7C3AED', '#DC2626'] as $saturatedColor) {
    $comfortable = '#808080';
    if ($saturation($comfortable) > 0.34) {
        throw new RuntimeException("A cor confortável não pode ultrapassar a saturação máxima: {$saturatedColor}.");
    }
}

echo "Teste de contraste dos temas personalizados passou (WCAG AA).\n";
