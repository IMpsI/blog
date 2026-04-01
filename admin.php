<?php
require_once __DIR__ . '/bootstrap.php';

if (!isset($_SESSION['logado']) || $_SESSION['nivel'] === 'leitor') {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'] ?? null;
$post = ['titulo' => '', 'conteudo' => ''];

if ($id) {
    $stmt = $db->prepare("SELECT * FROM posts WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $post = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    // Trava de segurança: apenas o autor ou admin edita
    if (!$post || ($_SESSION['nivel'] !== 'admin' && $post['autor_login'] !== $_SESSION['usuario_login'])) {
        header('Location: painel');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = htmlspecialchars($_POST['titulo']);
    $conteudo = $_POST['conteudo']; // TinyMCE ou HTML permitido aqui
    $autor = $_SESSION['usuario_login'];

    if ($id) {
        $stmt = $db->prepare("UPDATE posts SET titulo = :t, conteudo = :c WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    } else {
        $stmt = $db->prepare("INSERT INTO posts (titulo, conteudo, autor_login, data_publicacao) VALUES (:t, :c, :a, DATETIME('now'))");
        $stmt->bindValue(':a', $autor, SQLITE3_TEXT);
    }
    $stmt->bindValue(':t', $titulo, SQLITE3_TEXT);
    $stmt->bindValue(':c', $conteudo, SQLITE3_TEXT);
    $stmt->execute();
    header('Location: painel');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Estúdio - O Blog</title>
    <link rel="stylesheet" href="src/css/header.css">
    <link rel="stylesheet" href="src/css/style.css">
    <link rel="stylesheet" href="src/css/painel.css">
    <link rel="stylesheet" href="src/css/admin.css">
</head>

<body>
    <?php $busca = ''; require 'layout/header_pesquisa.php'; ?>

    <div class="container">
        <form method="POST" class="editor-box">
            <input type="text" name="titulo" placeholder="Título da sua história..." value="<?= htmlspecialchars($post['titulo']) ?>" required>
            <textarea name="conteudo" placeholder="Era uma vez..." required><?= htmlspecialchars($post['conteudo']) ?></textarea>

            <div class="actions">
                <a href="painel" class="btn btn-cancel">Descartar</a>
                <button type="submit" class="btn btn-save">Publicar História</button>
            </div>
        </form>
    </div>

    <script>
        if (localStorage.getItem('theme') === 'dark') document.body.classList.add('dark-mode');
        document.getElementById('themeBtn').addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
        });
    </script>
</body>

</html>