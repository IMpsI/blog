<?php
require_once __DIR__ . '/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
if (!loggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Faça login para reagir.']);
    exit;
}
$data = json_decode(file_get_contents('php://input'), true) ?: [];
$postId = (int) ($data['post_id'] ?? 0);
$type = $data['type'] ?? '';
if ($postId < 1 || !in_array($type, ['like', 'dislike'], true)) {
    http_response_code(422);
    echo json_encode(['error' => 'Dados inválidos.']);
    exit;
}
$current = dbOne('SELECT tipo_reacao FROM reacoes WHERE post_id = ? AND usuario_login = ?', [$postId, $_SESSION['user_login']]);
if (!$current) dbExec('INSERT INTO reacoes (post_id, usuario_login, tipo_reacao) VALUES (?, ?, ?)', [$postId, $_SESSION['user_login'], $type]);
elseif ($current['tipo_reacao'] === $type) dbExec('DELETE FROM reacoes WHERE post_id = ? AND usuario_login = ?', [$postId, $_SESSION['user_login']]);
else dbExec('UPDATE reacoes SET tipo_reacao = ? WHERE post_id = ? AND usuario_login = ?', [$type, $postId, $_SESSION['user_login']]);
echo json_encode(['likes' => (int) (dbOne("SELECT COUNT(*) AS total FROM reacoes WHERE post_id = ? AND tipo_reacao = 'like'", [$postId])['total'] ?? 0), 'dislikes' => (int) (dbOne("SELECT COUNT(*) AS total FROM reacoes WHERE post_id = ? AND tipo_reacao = 'dislike'", [$postId])['total'] ?? 0)]);
