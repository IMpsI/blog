<?php
require_once __DIR__ . '/bootstrap.php';

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

        $mensagem = "<h2 class='msg-confirmado'>Conta Confirmada!</h2><p>O seu e-mail foi validado com sucesso. Agora você já pode fazer login e interagir com as postagens.</p>";
    } else {
        $mensagem = "<h2 class='msg-invalido'>Link Inválido!</h2><p>O link que você acessou é inválido ou a sua conta já foi confirmada anteriormente.</p>";
    }
} else {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Confirmação de Conta</title>
    <link rel="stylesheet" href="src/css/header.css">
    <link rel="stylesheet" href="src/css/style.css">
    <link rel="stylesheet" href="src/css/painel.css">
    <link rel="stylesheet" href="src/css/confirmar_email.css">
</head>

<body>
    <?php $busca = ''; require 'layout/header_pesquisa.php'; ?>
    <div class="box">
        <?= $mensagem ?>
        <?php if (strpos($mensagem, 'msg-confirmado') !== false): ?>
            <a href="entrar" class="btn-login">Fazer Login Agora</a>
        <?php else: ?>
            <a href="index.php" class="btn-login">Voltar para o Início</a>
        <?php endif; ?>
    </div>
</body>

</html>