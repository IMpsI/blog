<?php
session_start();
require 'db.php';
require 'motor_email.php'; // Garante que o motor está carregado

$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $senha2 = $_POST['senha_confirma'] ?? '';

    if ($senha !== $senha2) {
        $mensagem = "<p style='color:#E67E22; text-align:center; font-weight:bold;'>As senhas não coincidem.</p>";
    } else {
        // Verifica se usuário ou e-mail já existem
        $check = $db->prepare('SELECT id FROM usuarios WHERE usuario = :u OR email = :e');
        $check->bindValue(':u', $usuario, SQLITE3_TEXT);
        $check->bindValue(':e', $email, SQLITE3_TEXT);

        if ($check->execute()->fetchArray()) {
            $mensagem = "<p style='color:#E74C3C; text-align:center; font-weight:bold;'>Usuário ou e-mail já cadastrado.</p>";
        } else {
            $token = bin2hex(random_bytes(16));
            $hash = password_hash($senha, PASSWORD_DEFAULT);

            $ins = $db->prepare("INSERT INTO usuarios (nome, usuario, email, senha, nivel, email_confirmado, token_autenticacao) VALUES (:n, :u, :e, :s, 'leitor', 0, :t)");
            $ins->bindValue(':n', $nome, SQLITE3_TEXT);
            $ins->bindValue(':u', $usuario, SQLITE3_TEXT);
            $ins->bindValue(':e', $email, SQLITE3_TEXT);
            $ins->bindValue(':s', $hash, SQLITE3_TEXT);
            $ins->bindValue(':t', $token, SQLITE3_TEXT);

            if ($ins->execute()) {
                // Monta o link de confirmação
                $link = "http://oblog.webredirect.org/confirmar-email?token=$token";

                $corpoEmail = "
                    <div style='font-family: Georgia, serif; color: #2C3E50;'>
                        <h1 style='color: #3498DB;'>Bem-vindo ao O Blog, $nome!</h1>
                        <p>Para começar a comentar e reagir às histórias, precisamos que você confirme seu e-mail.</p>
                        <p><a href='$link' style='background: #85C1E9; color: white; padding: 10px 20px; text-decoration: none; border-radius: 20px; font-weight: bold;'>Ativar Minha Conta</a></p>
                        <hr style='border: 0; border-top: 1px solid #EBF5FB;'>
                        <small>Se você não criou esta conta, ignore este e-mail.</small>
                    </div>
                ";

                // ENVIO REAL
                if (enviarEmail($email, $nome, "Ative sua conta - O Blog", $corpoEmail)) {
                    $mensagem = "<p style='color:#27AE60; text-align:center; font-weight:bold;'>Conta criada! Verifique seu e-mail para ativar.</p>";
                } else {
                    $mensagem = "<p style='color:#E67E22; text-align:center; font-weight:bold;'>Conta criada, mas houve um erro ao enviar o e-mail. Contate o administrador.</p>";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Criar Conta - O Blog</title>
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: 0.3s;
        }

        header {
            background: var(--paper);
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--border);
            width: 100%;
            box-sizing: border-box;
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
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .box {
            background: var(--paper);
            padding: 40px;
            border-radius: 15px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border);
        }

        h2 {
            text-align: center;
            font-family: 'Georgia', serif;
            font-weight: normal;
            margin-top: 0;
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
            font-family: inherit;
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
            font-size: 1rem;
        }

        .btn-submit:hover {
            background: var(--accent);
            transform: translateY(-2px);
        }

        .footer-link {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
        }

        .footer-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <header>
        <a href="/" class="logo">O Blog</a>
        <button class="btn-theme" id="themeBtn">Alternar Tema</button>
    </header>
    <div class="container">
        <div class="box">
            <h2>Criar Conta</h2>
            <?= $mensagem ?>
            <form method="POST">
                <input type="text" name="nome" placeholder="Seu nome completo" required>
                <input type="text" name="usuario" placeholder="Nome de usuário (Login)" required>
                <input type="email" name="email" placeholder="E-mail válido" required>
                <input type="password" name="senha" placeholder="Crie uma senha" required minlength="4">
                <input type="password" name="senha_confirma" placeholder="Confirme a senha" required>
                <button type="submit" class="btn-submit">Finalizar Cadastro</button>
            </form>
            <div class="footer-link">
                <span>Já tem uma conta? <a href="entrar">Fazer Login</a></span>
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