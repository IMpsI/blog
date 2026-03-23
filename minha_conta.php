<?php
session_start();
require 'db.php';

if (!isset($_SESSION['logado'])) {
    header('Location: entrar');
    exit;
}

$user_login = $_SESSION['usuario_login'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = htmlspecialchars($_POST['nome'] ?? '');
    $nome_artistico = htmlspecialchars($_POST['nome_artistico'] ?? '');

    $stmt = $db->prepare("UPDATE usuarios SET nome = :n, nome_artistico = :a WHERE usuario = :u");
    $stmt->bindValue(':n', $nome, SQLITE3_TEXT);
    $stmt->bindValue(':a', $nome_artistico, SQLITE3_TEXT);
    $stmt->bindValue(':u', $user_login, SQLITE3_TEXT);

    if ($stmt->execute()) {
        $_SESSION['autor_nome'] = $nome_artistico ?: $nome;
        $msg = "<p style='color:#27AE60; text-align:center; font-weight:bold;'>Perfil atualizado com sucesso!</p>";
    }
}

$u = $db->query("SELECT * FROM usuarios WHERE usuario = '$user_login'")->fetchArray(SQLITE3_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Meu Perfil - O Blog</title>
    <style>
        :root {
            --bg: #F0F8FF;
            --paper: #FFFFFF;
            --ink: #2C3E50;
            --ink-light: #5D6D7E;
            --baby-blue: #85C1E9;
            --accent: #3498DB;
            --border: #EBF5FB;
        }

        body.dark-mode {
            --bg: #0B1622;
            --paper: #15202B;
            --ink: #E1E8ED;
            --ink-light: #8899A6;
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
            min-height: 100vh;
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
            max-width: 500px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .profile-box {
            background: var(--paper);
            padding: 40px;
            border-radius: 15px;
            border: 1px solid var(--border);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        h2 {
            text-align: center;
            font-family: 'Georgia', serif;
            margin-top: 0;
            font-weight: normal;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 0.9rem;
            color: var(--ink-light);
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            background: var(--bg);
            border: 1px solid var(--baby-blue);
            color: var(--ink);
            border-radius: 25px;
            outline: none;
            box-sizing: border-box;
            font-family: inherit;
        }

        .btn-save {
            width: 100%;
            padding: 12px;
            background: var(--baby-blue);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-save:hover {
            background: var(--accent);
            transform: translateY(-2px);
        }
    </style>
</head>

<body>
    <header>
        <a href="/" class="logo">O Blog</a>
        <div style="display:flex; align-items:center; gap:20px;">
            <a href="painel" style="color:var(--accent); text-decoration:none; font-weight:bold;">Painel</a>
            <button class="btn-theme" id="themeBtn">Alternar Tema</button>
        </div>
    </header>

    <div class="container">
        <div class="profile-box">
            <h2>Configurações de Perfil</h2>
            <?= $msg ?>
            <form method="POST">
                <label>Nome Completo</label>
                <input type="text" name="nome" value="<?= htmlspecialchars($u['nome'] ?? '') ?>" required>

                <label>Nome Artístico (Nos posts)</label>
                <input type="text" name="nome_artistico" value="<?= htmlspecialchars($u['nome_artistico'] ?? '') ?>">

                <label>E-mail (Privado)</label>
                <input type="text" value="<?= htmlspecialchars($u['email'] ?? '') ?>" disabled style="opacity: 0.6; cursor: not-allowed;">

                <button type="submit" class="btn-save">Salvar Alterações</button>
            </form>
        </div>
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