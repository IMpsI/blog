<?php
require_once __DIR__ . '/bootstrap.php';
if (loggedIn()) redirect(($_SESSION['user_level'] ?? '') === 'leitor' ? 'index.php' : 'dashboard.php');
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = dbOne('SELECT * FROM usuarios WHERE usuario = ?', [trim($_POST['usuario'] ?? '')]);
    if (!$user || !password_verify($_POST['senha'] ?? '', $user['senha'])) {
        $message = 'Usuário ou senha incorretos.';
    } elseif (envFlag('BLOG_REQUIRE_EMAIL_CONFIRMATION') && $user['nivel'] === 'leitor' && !$user['email_confirmado']) {
        $message = 'Ative sua conta no e-mail antes de entrar.';
    } else {
        session_regenerate_id(true);
        $_SESSION['user_login'] = $user['usuario'];
        $_SESSION['user_level'] = $user['nivel'];
        $_SESSION['user_name'] = authorName($user);
        redirect($user['nivel'] === 'leitor' ? 'index.php' : 'dashboard.php');
    }
}
renderStart('Entrar');
?>
<main class="container auth-page">
    <div class="box auth-box">
        <h1>Acessar conta</h1><form
            method="post" class="form-grid"><input name="usuario" placeholder="Usuário" required><input type="password"
                name="senha" placeholder="Senha" required><button class="btn-submit">Entrar</button><?php if ($message): ?><p class="message error toast" role="alert"><?= e($message) ?></p><?php endif; ?></form>
        <p>Novo por aqui? <a href="register.php">Criar conta</a></p>
    </div>
</main>
<?php renderEnd(); ?>