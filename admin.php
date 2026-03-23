<?php
session_start();
require 'db.php';

if (!isset($_SESSION['logado']) || $_SESSION['nivel'] === 'leitor') {
    header('Location: /');
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
    <style>
        :root {
            --bg: #F0F8FF;
            --paper: #FFFFFF;
            --ink: #2C3E50;
            --baby-blue: #85C1E9;
            --accent: #3498DB;
            --border: #EBF5FB;
        }

        body.dark-mode {
            --bg: #0B1622;
            --paper: #15202B;
            --ink: #E1E8ED;
            --baby-blue: #1B91DA;
            --accent: #3498DB;
            --border: #1E2F3F;
        }

        body {
            background: var(--bg);
            color: var(--ink);
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            transition: 0.3s;
        }

        header {
            background: var(--paper);
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            font-size: 1.6rem;
            font-weight: bold;
            color: var(--accent);
            text-decoration: none;
            font-family: 'Georgia', serif;
            font-style: italic;
        }

        .btn-theme {
            background: none;
            border: 1px solid var(--baby-blue);
            border-radius: 20px;
            cursor: pointer;
            padding: 6px 15px;
            color: var(--ink);
            font-size: 0.8rem;
            font-weight: bold;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .editor-box {
            background: var(--paper);
            padding: 40px;
            border-radius: 15px;
            border: 1px solid var(--border);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        input[type="text"] {
            width: 100%;
            padding: 15px;
            font-size: 1.8rem;
            font-family: 'Georgia', serif;
            border: none;
            border-bottom: 2px solid var(--border);
            background: transparent;
            color: var(--ink);
            outline: none;
            margin-bottom: 30px;
        }

        input[type="text"]:focus {
            border-color: var(--accent);
        }

        textarea {
            width: 100%;
            min-height: 400px;
            padding: 15px;
            font-size: 1.1rem;
            font-family: 'Georgia', serif;
            border: 1px solid var(--border);
            background: var(--bg);
            color: var(--ink);
            border-radius: 10px;
            resize: vertical;
            outline: none;
            line-height: 1.6;
        }

        .actions {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }

        .btn {
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            transition: 0.3s;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .btn-save {
            background: var(--accent);
            color: white;
        }

        .btn-cancel {
            background: #BDC3C7;
            color: #2C3E50;
        }

        .btn:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }
    </style>
</head>

<body>
    <header>
        <a href="/" class="logo">O Blog <span style="font-size: 0.9rem; color: var(--ink-light); font-style: normal; font-weight: normal;">| Estúdio</span></a>
        <button class="btn-theme" id="themeBtn">Alternar Tema</button>
    </header>

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