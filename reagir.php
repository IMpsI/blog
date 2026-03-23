<?php
require_once __DIR__ . '/bootstrap.php';

// Diz ao navegador que a resposta será em formato JSON
header('Content-Type: application/json');

// Bloqueia quem não está logado
if (!isset($_SESSION['logado'])) {
    echo json_encode(['erro' => 'Não logado']);
    exit;
}

// Recebe os dados do JavaScript
$dados = json_decode(file_get_contents('php://input'), true);
$post_id = $dados['post_id'] ?? null;
$tipo = $dados['tipo'] ?? null;
$usuario = $_SESSION['usuario_login'];

if ($post_id && in_array($tipo, ['like', 'dislike'])) {
    // 1. Verifica se a pessoa já reagiu a este post
    $stmt = $db->prepare("SELECT tipo_reacao FROM reacoes WHERE post_id = :p AND usuario_login = :u");
    $stmt->bindValue(':p', $post_id, SQLITE3_INTEGER);
    $stmt->bindValue(':u', $usuario, SQLITE3_TEXT);
    $reacao_atual = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($reacao_atual) {
        if ($reacao_atual['tipo_reacao'] === $tipo) {
            // Se clicou no mesmo botão, remove a reação (Toggle off)
            $del = $db->prepare("DELETE FROM reacoes WHERE post_id = :p AND usuario_login = :u");
            $del->bindValue(':p', $post_id, SQLITE3_INTEGER);
            $del->bindValue(':u', $usuario, SQLITE3_TEXT);
            $del->execute();
        } else {
            // Se trocou de Like para Dislike (ou vice-versa), atualiza
            $upd = $db->prepare("UPDATE reacoes SET tipo_reacao = :t WHERE post_id = :p AND usuario_login = :u");
            $upd->bindValue(':t', $tipo, SQLITE3_TEXT);
            $upd->bindValue(':p', $post_id, SQLITE3_INTEGER);
            $upd->bindValue(':u', $usuario, SQLITE3_TEXT);
            $upd->execute();
        }
    } else {
        // Se nunca reagiu, insere uma nova
        $ins = $db->prepare("INSERT INTO reacoes (post_id, usuario_login, tipo_reacao) VALUES (:p, :u, :t)");
        $ins->bindValue(':p', $post_id, SQLITE3_INTEGER);
        $ins->bindValue(':u', $usuario, SQLITE3_TEXT);
        $ins->bindValue(':t', $tipo, SQLITE3_TEXT);
        $ins->execute();
    }

    // Conta quantos likes e dislikes ficaram no total para devolver para a tela
    $likes = $db->querySingle("SELECT COUNT(*) FROM reacoes WHERE post_id = $post_id AND tipo_reacao = 'like'");
    $dislikes = $db->querySingle("SELECT COUNT(*) FROM reacoes WHERE post_id = $post_id AND tipo_reacao = 'dislike'");

    echo json_encode(['sucesso' => true, 'likes' => $likes, 'dislikes' => $dislikes]);
} else {
    echo json_encode(['erro' => 'Dados inválidos']);
}
