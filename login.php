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
            $mensagem = "<p class='msg-warning'>Ative sua conta no e-mail primeiro.</p>";
        } else {
            $_SESSION['logado'] = true;
            $_SESSION['usuario_login'] = $user['usuario'];
            $_SESSION['nivel'] = $user['nivel'];
            $_SESSION['autor_nome'] = $user['nome_artistico'] ?: ($user['nome'] ?: 'Anônimo');
            header('Location: ' . ($user['nivel'] === 'leitor' ? 'index.php' : 'painel'));
            exit;
        }
    } else {
        $mensagem = "<p class='msg-error'>Usuário ou senha incorretos.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Login - O Blog</title>
    <link rel="stylesheet" href="src/css/header.css">
    <link rel="stylesheet" href="src/css/style.css">
    <link rel="stylesheet" href="src/css/painel.css">
    <link rel="stylesheet" href="src/css/login.css">
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