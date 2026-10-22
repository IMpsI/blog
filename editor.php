<?php
require_once __DIR__ . '/bootstrap.php';
requireEditor();
$id = (int) ($_GET['id'] ?? 0);
$post = $id ? dbOne('SELECT * FROM posts WHERE id = ?', [$id]) : null;
if ($id && (!$post || ($_SESSION['user_level'] !== 'admin' && $post['autor_login'] !== $_SESSION['user_login']))) redirect('dashboard.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['titulo'] ?? '');
    $content = trim($_POST['conteudo'] ?? '');
    if ($id) dbExec('UPDATE posts SET titulo = ?, conteudo = ?, data_atualizacao = NOW() WHERE id = ?', [$title, $content, $id]);
    else dbExec('INSERT INTO posts (titulo, conteudo, autor_login) VALUES (?, ?, ?)', [$title, $content, $_SESSION['user_login']]);
    redirect('dashboard.php');
}
renderStart($id ? 'Editar história' : 'Nova história');
?>
<main class="container">
    <div class="box">
        <h1><?= $id ? 'Editar história' : 'Nova história' ?></h1>
        <form method="post" class="form-grid"><input name="titulo" value="<?= e($post['titulo'] ?? '') ?>"
                placeholder="Título da história" required><textarea name="conteudo"
                placeholder="Escreva sua história..." required><?= e($post['conteudo'] ?? '') ?></textarea>
            <div><a class="button" href="dashboard.php">Cancelar</a> <button class="btn-submit">Salvar história</button>
            </div>
        </form>
    </div>
</main>
<?php renderEnd(); ?>