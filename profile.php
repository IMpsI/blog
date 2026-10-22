<?php
require_once __DIR__ . '/bootstrap.php';
requireLogin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    dbExec('UPDATE usuarios SET nome = ?, nome_artistico = ? WHERE usuario = ?', [trim($_POST['nome']), trim($_POST['nome_artistico'] ?? ''), $_SESSION['user_login']]);
    $user = currentUser();
    $_SESSION['user_name'] = authorName($user);
}
$user = currentUser();
renderStart('Meu perfil');
?>
<main class="container">
    <div class="box">
        <h1>Configurações de perfil</h1>
        <form method="post" class="form-grid"><label>Nome<input name="nome" value="<?= e($user['nome'] ?? '') ?>"
                    required></label><label>Nome artístico<input name="nome_artistico"
                    value="<?= e($user['nome_artistico'] ?? '') ?>"></label><label>E-mail<input
                    value="<?= e($user['email'] ?? '') ?>" disabled></label><button class="btn-submit">Salvar
                alterações</button></form>
    </div>
</main>
<?php renderEnd(); ?>