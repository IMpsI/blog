<?php
require_once __DIR__ . '/bootstrap.php';

if (isset($_SESSION['logado'])) {
    header('Location: ' . ($_SESSION['nivel'] === 'leitor' ? 'index.php' : 'painel'));
    exit;
}

$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario']);
    $senha = $_POST['senha'];
    $stmt = $db->prepare('SELECT * FROM usuarios WHERE usuario = :u');
    $stmt->bindValue(':u', $usuario, SQLITE3_TEXT);
    $user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($user && password_verify($senha, $user['senha'])) {
        if ($user['nivel'] === 'leitor' && $user['email_confirmado'] == 0) {
            $mensagem = "<p style='color: #E67E22; text-align: center; font-weight: bold;'>Ative sua conta no e-mail primeiro.</p>";
        } else {
            $_SESSION['logado'] = true;
            $_SESSION['usuario_login'] = $user['usuario'];
            $_SESSION['nivel'] = $user['nivel'];
            $_SESSION['autor_nome'] = $user['nome_artistico'] ?: ($user['nome'] ?: 'Anônimo');
            header('Location: ' . ($user['nivel'] === 'leitor' ? 'index.php' : 'painel'));
            exit;
        }
    } else {
        $mensagem = "<p style='color: #E74C3C; text-align: center;'>Usuário ou senha incorretos.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Login - O Blog</title>
    <link rel="stylesheet" href="src/css/style.css">
    <link rel="stylesheet" href="src/css/painel.css">
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: 0.3s;
        }

        /* Ajuste do Header */
        header {
            background: var(--paper);
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--border);
            box-sizing: border-box;
            width: 100%;
        }

        .logo {
            font-size: 1.6rem;
            font-weight: bold;
            color: var(--accent);
            text-decoration: none;
            font-family: 'Georgia', serif;
            font-style: italic;
        }

        /* Estilo do Botão de Tema */
        .btn-theme {
            background: none;
            border: 1px solid var(--baby-blue);
            border-radius: 20px;
            cursor: pointer;
            padding: 6px 15px;
            color: var(--ink);
            font-size: 0.8rem;
            font-weight: bold;
            transition: 0.3s;
            white-space: nowrap;
        }

        .btn-theme:hover {
            background: var(--baby-blue);
            color: white;
        }

        .container {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .box {
            background: var(--paper);
            padding: 40px;
            border-radius: 15px;
            width: 100%;
            max-width: 350px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border);
            text-align: center;
        }

        h2 {
            margin-top: 0;
            font-family: 'Georgia', serif;
            color: var(--ink);
            font-weight: normal;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            background: var(--bg);
            border: 1px solid var(--baby-blue);
            color: var(--ink);
            border-radius: 25px;
            outline: none;
            box-sizing: border-box;
        }

        .btn-submit {
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

        .btn-submit:hover {
            background: var(--accent);
            transform: translateY(-2px);
        }

        .links {
            margin-top: 20px;
            font-size: 0.9rem;
        }

        .links a {
            color: var(--accent);
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php $busca = ''; require 'layout/header_pesquisa.php'; ?>
    <div class="container">
        <div class="box">
            <h2>Acessar Conta</h2>
            <?= $mensagem ?>
            <form method="POST">
                <input type="text" name="usuario" placeholder="Usuário" required>
                <input type="password" name="senha" placeholder="Senha" required>
                <button type="submit" class="btn-submit">Entrar</button>
            </form>
            <div class="links">
                <a href="recuperar-senha">Esqueci minha senha</a><br><br>
                <span>Novo por aqui? <a href="cadastro">Criar conta</a></span>
            </div>
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