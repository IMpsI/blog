<?php 
// --- 3. BUSCA DE POSTS ---
$busca = $_GET['q'] ?? '';
$sql = "SELECT posts.*, usuarios.nome_artistico, usuarios.nome,
        (SELECT COUNT(*) FROM comentarios WHERE post_id = posts.id) AS qtd_coment,
        (SELECT COUNT(*) FROM reacoes WHERE post_id = posts.id AND tipo_reacao = 'like') AS qtd_likes,
        (SELECT COUNT(*) FROM reacoes WHERE post_id = posts.id AND tipo_reacao = 'dislike') AS qtd_dislikes
        FROM posts LEFT JOIN usuarios ON posts.autor_login = usuarios.usuario";
if ($busca) $sql .= " WHERE posts.titulo LIKE :b OR posts.conteudo LIKE :b";
$sql .= " ORDER BY posts.data_publicacao DESC";

$stmt = $db->prepare($sql);
if ($busca) $stmt->bindValue(':b', "%$busca%", SQLITE3_TEXT);
$posts = $stmt->execute();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>O Blog - Biblioteca Digital</title>
<link rel="stylesheet" href="./src/css/style.css">
<link rel="stylesheet" href="./src/css/painel.css">
</head>
<body>