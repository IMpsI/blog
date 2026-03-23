<?php
require_once __DIR__ . '/bootstrap.php';

// TRAVA NÍVEL LEITOR: Expulsa os leitores que tentarem forçar a URL
if ($_SESSION['nivel'] === 'leitor') {
    header('Location: index.php');
    exit;
}

// Se não estiver logado, cai fora
if (!isset($_SESSION['logado'])) {
    header('Location: entrar');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: painel');
    exit;
}

$user_login = $_SESSION['usuario_login'];
$is_admin = ($_SESSION['nivel'] === 'admin');

// Busca o autor da postagem para verificar a permissão
$stmt = $db->prepare("SELECT autor_login FROM posts WHERE id = :id");
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$post = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if ($post) {
    // Permissão: Pode apagar se for o Admin OU se for o dono da postagem
    if ($is_admin || $post['autor_login'] === $user_login) {
        $del = $db->prepare("DELETE FROM posts WHERE id = :id");
        $del->bindValue(':id', $id, SQLITE3_INTEGER);
        $del->execute();
    }
}

// Redireciona de volta para a dashboard assim que terminar
header('Location: painel');
exit;
