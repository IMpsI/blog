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
        $mensagem = "<p style='color: #4CAF50; text-align: center; font-weight: bold;'>Postagem atualizada com sucesso!</p>";
    } else {
        $mensagem = "<p style='color: #F44336; text-align: center;'>Erro ao atualizar a postagem.</p>";
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
    echo "<body style='background:#121212; color:white; font-family:sans-serif; text-align:center; padding-top:100px;'>";
    echo "<h1 style='color:#F44336;'>ACESSO NEGADO</h1>";
    echo "<p>Você não tem permissão para editar uma postagem que pertence a outro autor.</p>";
    echo "<a href='painel' style='color:#2196F3;'>Voltar ao Painel</a>";
    echo "</body>";
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
    <link rel="stylesheet" href="src/css/style.css">
    <link rel="stylesheet" href="src/css/painel.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>
    <style>
        body {
            background-color: #121212;
            color: #e0e0e0;
            font-family: sans-serif;
            margin: 0;
            padding: 40px;
            display: flex;
            justify-content: center;
            position: relative;
        }

        .container {
            width: 100%;
            max-width: 900px;
            background: #1e1e1e;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        h1 {
            text-align: center;
            font-weight: 300;
            letter-spacing: 2px;
            margin-bottom: 30px;
            color: #2196F3;
        }

        input[type="text"] {
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            background: #2d2d2d;
            border: 1px solid #444;
            color: white;
            font-size: 1.2rem;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button.btn-pub {
            width: 100%;
            padding: 15px;
            margin-top: 20px;
            background: #2196F3;
            color: white;
            border: none;
            font-size: 1.1rem;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        button.btn-pub:hover {
            background: #1976D2;
        }

        .btn-top {
            position: absolute;
            top: 20px;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 0.9rem;
            transition: 0.3s;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .btn-dash {
            right: 30px;
            background-color: #555;
            color: white;
        }

        .tox-notification {
            display: none !important;
        }
    </style>
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