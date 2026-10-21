<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/motor_email.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['nome'] ?? '');
    $username = trim($_POST['usuario'] ?? '');
    $email = trim($_POST['email'] ?? '');
    if ($_POST['senha'] !== ($_POST['senha_confirma'] ?? '')) {
        $message = 'As senhas não coincidem.';
    } elseif (dbOne('SELECT id FROM usuarios WHERE usuario = ? OR email = ?', [$username, $email])) {
        $message = 'Usuário ou e-mail já cadastrado.';
    } else {
        $token = bin2hex(random_bytes(16));
        $confirmed = envFlag('BLOG_REQUIRE_EMAIL_CONFIRMATION');
        dbExec('INSERT INTO usuarios (nome, usuario, email, senha, email_confirmado, token_autenticacao) VALUES (?, ?, ?, ?, ?, ?)', [$name, $username, $email, password_hash($_POST['senha'], PASSWORD_DEFAULT), $confirmed ? 0 : 1, $token]);
        if ($confirmed) {
            $link = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/confirm_email.php?token=' . $token;
            $sent = sendEmail($email, $name, 'Confirme sua conta - O Blog', '<p>Olá, ' . e($name) . '.</p><p><a href="' . e($link) . '">Ativar minha conta</a></p>');
            $message = $sent ? 'Conta criada. Confira seu e-mail para ativá-la.' : 'Conta criada, mas não foi possível enviar o e-mail de confirmação.';
        } else {
            $message = 'Conta criada. Você já pode entrar.';
        }
    }
}
renderStart('Criar conta');
?>
<main class="container"><div class="box"><h1>Criar conta</h1><?php if ($message): ?><p class="message"><?= e($message) ?></p><?php endif; ?><form method="post" class="form-grid"><input name="nome" placeholder="Nome completo" required><input name="usuario" placeholder="Usuário" required><input type="email" name="email" placeholder="E-mail" required><input type="password" name="senha" minlength="4" placeholder="Senha" required><input type="password" name="senha_confirma" placeholder="Confirme a senha" required><button class="btn-submit">Finalizar cadastro</button></form><p>Já tem conta? <a href="login.php">Entrar</a></p></div></main>
<?php renderEnd(); ?>
