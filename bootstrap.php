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
    require __DIR__ . '/layout/head.php';
    require __DIR__ . '/layout/header.php';
}

function renderEnd(): void
{
    require __DIR__ . '/layout/scripts.php';
    require __DIR__ . '/layout/footer.php';
}
