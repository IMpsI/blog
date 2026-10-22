<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/db.php';

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $location): never
{
    header('Location: ' . $location);
    exit;
}

function loggedIn(): bool
{
    return isset($_SESSION['user_login']);
}

function requireLogin(): void
{
    if (!loggedIn()) {
        redirect('login.php');
    }
}

function requireEditor(): void
{
    requireLogin();
    if (($_SESSION['user_level'] ?? 'leitor') === 'leitor') {
        redirect('index.php');
    }
}

function requireAdmin(): void
{
    requireLogin();
    if (($_SESSION['user_level'] ?? '') !== 'admin') {
        redirect('index.php');
    }
}

function currentUser(): ?array
{
    return loggedIn() ? dbOne('SELECT * FROM usuarios WHERE usuario = ?', [$_SESSION['user_login']]) : null;
}

function validThemeColor(?string $color): ?string
{
    $color = strtoupper(trim((string) $color));
    return preg_match('/^#[0-9A-F]{6}$/', $color) ? $color : null;
}

function themeRelativeLuminance(string $color): float
{
    $channels = [];
    foreach ([0, 2, 4] as $offset) {
        $channel = hexdec(substr($color, $offset + 1, 2)) / 255;
        $channels[] = $channel <= 0.03928
            ? $channel / 12.92
            : (($channel + 0.055) / 1.055) ** 2.4;
    }

    return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
}

function themeTextColor(string $background): string
{
    $luminance = themeRelativeLuminance($background);
    $whiteContrast = (1.0 + 0.05) / ($luminance + 0.05);
    $blackContrast = ($luminance + 0.05) / 0.05;

    return $whiteContrast >= $blackContrast ? '#FFFFFF' : '#111827';
}

function themeHexToHsl(string $color): array
{
    [$red, $green, $blue] = array_map(
        static fn (string $channel): float => hexdec($channel) / 255,
        str_split(substr($color, 1), 2)
    );
    $max = max($red, $green, $blue);
    $min = min($red, $green, $blue);
    $lightness = ($max + $min) / 2;
    if ($max === $min) {
        return [0.0, 0.0, $lightness];
    }
    $delta = $max - $min;
    $saturation = $lightness > 0.5 ? $delta / (2 - $max - $min) : $delta / ($max + $min);
    $hue = match ($max) {
        $red => (($green - $blue) / $delta) + ($green < $blue ? 6 : 0),
        $green => (($blue - $red) / $delta) + 2,
        default => (($red - $green) / $delta) + 4,
    };
    return [$hue / 6, $saturation, $lightness];
}

function themeHslToHex(float $hue, float $saturation, float $lightness): string
{
    if ($saturation === 0.0) {
        $channel = (int) round($lightness * 255);
        return sprintf('#%02X%02X%02X', $channel, $channel, $channel);
    }
    $hueToChannel = static function (float $p, float $q, float $t): float {
        if ($t < 0) $t += 1;
        if ($t > 1) $t -= 1;
        if ($t < 1 / 6) return $p + ($q - $p) * 6 * $t;
        if ($t < 1 / 2) return $q;
        if ($t < 2 / 3) return $p + ($q - $p) * (2 / 3 - $t) * 6;
        return $p;
    };
    $q = $lightness < 0.5
        ? $lightness * (1 + $saturation)
        : $lightness + $saturation - $lightness * $saturation;
    $p = 2 * $lightness - $q;
    $red = $hueToChannel($p, $q, $hue + 1 / 3);
    $green = $hueToChannel($p, $q, $hue);
    $blue = $hueToChannel($p, $q, $hue - 1 / 3);
    return sprintf('#%02X%02X%02X', round($red * 255), round($green * 255), round($blue * 255));
}

function themeComfortColor(string $color, float $maximumSaturation = 0.34): string
{
    [$hue, $saturation, $lightness] = themeHexToHsl($color);
    return themeHslToHex($hue, min($saturation, $maximumSaturation), $lightness);
}

function themeReadableSecondary(string $primary, string $secondary): string
{
    $comfortable = themeComfortColor($secondary);
    [$hue, $saturation] = themeHexToHsl($comfortable);
    $best = $comfortable;
    $bestContrast = contrastRatio($comfortable, $primary);
    for ($step = 0; $step <= 100; $step++) {
        $candidate = themeHslToHex($hue, $saturation, $step / 100);
        $candidateContrast = contrastRatio($candidate, $primary);
        if ($candidateContrast > $bestContrast) {
            $best = $candidate;
            $bestContrast = $candidateContrast;
        }
    }
    return $bestContrast >= 7.0 ? $best : themeTextColor($primary);
}

function themeSurfaceColor(string $primary, bool $dark, float $weight = 0.0): string
{
    [$red, $green, $blue] = array_map(
        static fn (string $channel): int => hexdec($channel),
        str_split(substr(themeComfortColor($primary, 0.28), 1), 2)
    );
    $weight = $weight > 0 ? $weight : ($dark ? 0.20 : 0.12);
    $target = $dark ? 0 : 255;
    return sprintf(
        '#%02X%02X%02X',
        round($red * $weight + $target * (1 - $weight)),
        round($green * $weight + $target * (1 - $weight)),
        round($blue * $weight + $target * (1 - $weight))
    );
}

function contrastRatio(string $foreground, string $background): float
{
    $foregroundLuminance = themeRelativeLuminance($foreground);
    $backgroundLuminance = themeRelativeLuminance($background);
    return (max($foregroundLuminance, $backgroundLuminance) + 0.05)
        / (min($foregroundLuminance, $backgroundLuminance) + 0.05);
}

function themeStyle(): string
{
    $user = currentUser();
    $primary = validThemeColor($user['tema_cor_principal'] ?? null);
    $secondary = validThemeColor($user['tema_cor_secundaria'] ?? null);

    if ($primary === null || $secondary === null) {
        return '';
    }

    $lightSurface = themeSurfaceColor($primary, false);
    $darkSurface = themeSurfaceColor($primary, true);
    $lightHeader = themeSurfaceColor($primary, false, 0.18);
    $darkHeader = themeSurfaceColor($primary, true, 0.28);
    $lightContent = themeReadableSecondary($lightSurface, $secondary);
    $darkContent = themeReadableSecondary($darkSurface, $secondary);
    $lightHeaderText = themeReadableSecondary($lightHeader, $secondary);
    $darkHeaderText = themeReadableSecondary($darkHeader, $secondary);
    $style = ' style="--custom-primary: %s; --custom-primary-ink: %s; '
        . '--custom-secondary: %s; --custom-surface-light: %s; '
        . '--custom-surface-dark: %s; --custom-secondary-readable-light: %s; '
        . '--custom-secondary-readable-dark: %s; --custom-secondary-ink-light: %s; '
        . '--custom-secondary-ink-dark: %s; --custom-header-light: %s; '
        . '--custom-header-dark: %s; --custom-header-text-light: %s; '
        . '--custom-header-text-dark: %s;" class="custom-theme"';
    return sprintf(
        $style,
        e($primary),
        e(themeTextColor($primary)),
        e($secondary),
        e($lightSurface),
        e($darkSurface),
        e($lightContent),
        e($darkContent),
        e(themeTextColor($lightContent)),
        e(themeTextColor($darkContent)),
        e($lightHeader),
        e($darkHeader),
        e($lightHeaderText),
        e($darkHeaderText)
    );
}

function authorName(?array $user): string
{
    return trim((string) ($user['nome_artistico'] ?? '')) ?: trim((string) ($user['nome'] ?? '')) ?: 'Anônimo';
}

function postQuery(string $where = '', array $params = []): array
{
    return dbAll(
        "SELECT p.*, u.nome, u.nome_artistico,
            (SELECT COUNT(*) FROM comentarios c WHERE c.post_id = p.id) AS qtd_coment,
            (SELECT COUNT(*) FROM reacoes r WHERE r.post_id = p.id AND r.tipo_reacao = 'like') AS qtd_likes,
            (SELECT COUNT(*) FROM reacoes r WHERE r.post_id = p.id AND r.tipo_reacao = 'dislike') AS qtd_dislikes
         FROM posts p LEFT JOIN usuarios u ON u.usuario = p.autor_login {$where}
         ORDER BY p.data_publicacao DESC",
        $params
    );
}

function renderComments(int $postId, int $limit = 10): array
{
    return dbAll(
        'SELECT c.*, u.nome, u.nome_artistico, u.usuario FROM comentarios c
         LEFT JOIN usuarios u ON u.usuario = c.usuario_login
         WHERE c.post_id = ? ORDER BY c.data_comentario DESC LIMIT ' . max(1, $limit),
        [$postId]
    );
}

function handleComment(int $postId): void
{
    requireLogin();
    if (isset($_POST['delete_comment'])) {
        $comment = dbOne('SELECT usuario_login FROM comentarios WHERE id = ? AND post_id = ?', [(int) $_POST['delete_comment'], $postId]);
        if ($comment && (($_SESSION['user_level'] ?? '') === 'admin' || $comment['usuario_login'] === $_SESSION['user_login'])) {
            dbExec('DELETE FROM comentarios WHERE id = ?', [(int) $_POST['delete_comment']]);
        }
    } elseif (isset($_POST['edit_comment'])) {
        dbExec('UPDATE comentarios SET comentario = ?, editado = 1 WHERE id = ? AND usuario_login = ?', [
            trim((string) $_POST['comment']),
            (int) $_POST['edit_comment'],
            $_SESSION['user_login'],
        ]);
    } elseif (trim((string) ($_POST['comment'] ?? '')) !== '') {
        dbExec('INSERT INTO comentarios (post_id, usuario_login, comentario) VALUES (?, ?, ?)', [
            $postId,
            $_SESSION['user_login'],
            trim((string) $_POST['comment']),
        ]);
    }
}

function renderStart(string $title, array $styles = []): void
{
    $pageTitle = $title . ' - ' . APP_NAME;
    $bodyTheme = themeStyle();
    require __DIR__ . '/layout/head.php';
    require __DIR__ . '/layout/header.php';
}

function renderEnd(): void
{
    require __DIR__ . '/layout/scripts.php';
    require __DIR__ . '/layout/footer.php';
}
