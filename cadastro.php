<?php
require_once __DIR__ . '/bootstrap.php';
require 'motor_email.php'; // Garante que o motor está carregado

$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $senha2 = $_POST['senha_confirma'] ?? '';

    if ($senha !== $senha2) {
        $mensagem = "<p class='msg-warning'>As senhas não coincidem.</p>";
    } else {
        // Verifica se usuário ou e-mail já existem
        $check = $db->prepare('SELECT id FROM usuarios WHERE usuario = :u OR email = :e');
        $check->bindValue(':u', $usuario, SQLITE3_TEXT);
        $check->bindValue(':e', $email, SQLITE3_TEXT);

        if ($check->execute()->fetchArray()) {
            $mensagem = "<p class='msg-error'>Usuário ou e-mail já cadastrado.</p>";
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
                    $mensagem = "<p class='msg-success'>Conta criada! Verifique seu e-mail para ativar.</p>";
                } else {
                    $mensagem = "<p class='msg-warning'>Conta criada, mas houve um erro ao enviar o e-mail. Contate o administrador.</p>";
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
    <link rel="stylesheet" href="src/css/header.css">
    <link rel="stylesheet" href="src/css/style.css">
    <link rel="stylesheet" href="src/css/painel.css">
    <link rel="stylesheet" href="src/css/cadastro.css">
</head>

<body>
    <?php $busca = ''; require 'layout/header_pesquisa.php'; ?>
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