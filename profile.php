<?php
require_once __DIR__ . '/bootstrap.php';
requireLogin();
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resetTheme = isset($_POST['reset_theme']);
    $primary = validThemeColor($_POST['tema_cor_principal'] ?? null);
    $secondary = validThemeColor($_POST['tema_cor_secundaria'] ?? null);
    if ($resetTheme) {
        dbExec(
            'UPDATE usuarios SET nome = ?, nome_artistico = ?, tema_cor_principal = NULL, tema_cor_secundaria = NULL WHERE usuario = ?',
            [trim($_POST['nome'] ?? ''), trim($_POST['nome_artistico'] ?? ''), $_SESSION['user_login']]
        );
        $message = 'Tema padrão restaurado.';
    } elseif ($primary === null || $secondary === null) {
        $message = 'Escolha duas cores válidas para salvar seu tema.';
    } else {
        dbExec(
            'UPDATE usuarios SET nome = ?, nome_artistico = ?, tema_cor_principal = ?, tema_cor_secundaria = ? WHERE usuario = ?',
            [trim($_POST['nome'] ?? ''), trim($_POST['nome_artistico'] ?? ''), $primary, $secondary, $_SESSION['user_login']]
        );
        $user = currentUser();
        $_SESSION['user_name'] = authorName($user);
        $message = 'Perfil e tema salvos.';
    }
}
$user = currentUser();
renderStart('Meu perfil');
?>
<main class="container">
    <div class="box">
        <h1>Configurações de perfil</h1>
        <form method="post" class="form-grid">
            <label>Nome<input name="nome" value="<?= e($user['nome'] ?? '') ?>" required></label>
            <label>Nome artístico<input name="nome_artistico" value="<?= e($user['nome_artistico'] ?? '') ?>"></label>
            <label>E-mail<input value="<?= e($user['email'] ?? '') ?>" disabled></label>
            <fieldset class="theme-fields">
                <legend>Tema personalizado</legend>
                <p>Escolha duas cores para personalizar o visual da sua conta. A configuração acompanha você em qualquer
                    dispositivo.</p>
                <label>Cor principal<input type="color" name="tema_cor_principal"
                        value="<?= e($user['tema_cor_principal'] ?? '#1769AA') ?>" required></label>
                <label>Cor secundária<input type="color" name="tema_cor_secundaria"
                        value="<?= e($user['tema_cor_secundaria'] ?? '#EEF7FD') ?>" required></label>
            </fieldset>
            <button class="btn-submit">Salvar alterações</button>
            <button class="button theme-reset" type="submit" name="reset_theme" value="1">Voltar ao tema padrão</button>
            <?php if ($message): ?><p class="message toast" role="alert"><?= e($message) ?></p><?php endif; ?>
        </form>
    </div>
</main>
<?php renderEnd(); ?>