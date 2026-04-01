<?php
require_once __DIR__ . '/bootstrap.php';

// TRAVA NÍVEL LEITOR: Expulsa os leitores que tentarem forçar a URL
if ($_SESSION['nivel'] === 'leitor') {
    header('Location: index.php');
    exit;
}


if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header('Location: entrar');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: painel');
    exit;
}

$mensagem = '';

// DML: Atualiza o post e registra a data/hora exata da alteração
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $conteudo = $_POST['conteudo'];

    // O comando CURRENT_TIMESTAMP pega a data e hora do exato momento do clique
    $stmt = $db->prepare('UPDATE posts SET titulo = :titulo, conteudo = :conteudo, data_atualizacao = CURRENT_TIMESTAMP WHERE id = :id');
    $stmt->bindValue(':titulo', $titulo, SQLITE3_TEXT);
    $stmt->bindValue(':conteudo', $conteudo, SQLITE3_TEXT);
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);

    if ($stmt->execute()) {
        $mensagem = "<p class='msg-success'>Postagem atualizada com sucesso!</p>";
    } else {
        $mensagem = "<p class='msg-error'>Erro ao atualizar a postagem.</p>";
    }
}

// DQL: Busca os dados atuais
$stmt = $db->prepare("SELECT * FROM posts WHERE id = :id");
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
// Se o post não existir no banco, volta pro painel
if (!$post) {
    header('Location: painel');
    exit;
}

// TRAVA DE SEGURANÇA (ABAC): Se quem está tentando editar NÃO é o autor da postagem, bloqueia!
if ($post['autor_login'] !== $_SESSION['usuario_login']) {
    echo "<!DOCTYPE html>";
    echo "<html lang='pt-BR'><head><meta charset='UTF-8'><title>Acesso Negado</title>";
    echo "<link rel='stylesheet' href='src/css/editar_post.css'></head>";
    echo "<body class='access-denied'>";
    echo "<h1 class='access-denied-title'>ACESSO NEGADO</h1>";
    echo "<p>Você não tem permissão para editar uma postagem que pertence a outro autor.</p>";
    echo "<a href='painel' class='access-denied-link'>Voltar ao Painel</a>";
    echo "</body></html>";
    exit;
};

if (!$post) {
    header('Location: painel');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Editar Postagem</title>
    <link rel="stylesheet" href="src/css/header.css">
    <link rel="stylesheet" href="src/css/style.css">
    <link rel="stylesheet" href="src/css/painel.css">
    <link rel="stylesheet" href="src/css/editar_post.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: '#editor',
            skin: 'oxide-dark',
            content_css: 'dark',
            menubar: false,
            plugins: 'lists link image media table code wordcount',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link blockquote',
            height: 500
        });
    </script>
</head>

<body>
    <?php $busca = ''; require 'layout/header_pesquisa.php'; ?>
    <a href="painel" class="btn-top btn-dash">← Voltar ao Painel</a>

    <div class="container">
        <h1>EDITAR POSTAGEM</h1>
        <?= $mensagem ?>

        <form method="POST">
            <input type="text" name="titulo" value="<?= htmlspecialchars($post['titulo']) ?>" required>
            <textarea id="editor" name="conteudo"><?= htmlspecialchars($post['conteudo']) ?></textarea>
            <button type="submit" class="btn-pub">Salvar Alterações</button>
        </form>
    </div>
</body>

</html>