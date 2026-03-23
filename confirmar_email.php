<?php
require 'db.php';

$mensagem = '';
$token = $_GET['token'] ?? null;

if ($token) {
    // Procura o usuário que tem esse token
    $stmt = $db->prepare('SELECT id FROM usuarios WHERE token_autenticacao = :t');
    $stmt->bindValue(':t', $token, SQLITE3_TEXT);
    $user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($user) {
        // Encontrou! Atualiza a conta para confirmada e limpa o token
        $update = $db->prepare('UPDATE usuarios SET email_confirmado = 1, token_autenticacao = NULL WHERE id = :id');
        $update->bindValue(':id', $user['id'], SQLITE3_INTEGER);
        $update->execute();

        $mensagem = "<h2 style='color: #4CAF50;'>Conta Confirmada!</h2><p>O seu e-mail foi validado com sucesso. Agora você já pode fazer login e interagir com as postagens.</p>";
    } else {
        $mensagem = "<h2 style='color: #F44336;'>Link Inválido!</h2><p>O link que você acessou é inválido ou a sua conta já foi confirmada anteriormente.</p>";
    }
} else {
    header('Location: /');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Confirmação de Conta</title>
    <style>
        body {
            background: #121212;
            color: white;
            font-family: sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .box {
            background: #1e1e1e;
            padding: 40px;
            border-radius: 10px;
            width: 400px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .btn-login {
            display: inline-block;
            padding: 12px 25px;
            background: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="box">
        <?= $mensagem ?>
        <?php if (strpos($mensagem, '#4CAF50') !== false): ?>
            <a href="entrar" class="btn-login">Fazer Login Agora</a>
        <?php else: ?>
            <a href="/" class="btn-login">Voltar para o Início</a>
        <?php endif; ?>
    </div>
</body>

</html>