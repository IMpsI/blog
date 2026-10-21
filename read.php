<?php
require_once __DIR__ . '/bootstrap.php';
$id = (int) ($_GET['id'] ?? 0);
$post = postQuery('WHERE p.id = ?', [$id])[0] ?? null;
if (!$post) {
    redirect('index.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handleComment($id);
    redirect('read.php?id=' . $id);
}

renderStart($post['titulo']);
?>
<main class="container">
    <article class="post">
        <h1><?= e($post['titulo']) ?></h1>
        <div class="meta"><span>Por <strong><?= e(authorName($post)) ?></strong> · <?= date('d/m/Y', strtotime($post['data_publicacao'])) ?></span><span><?= max(1, (int) ceil(str_word_count(strip_tags($post['conteudo'])) / 200)) ?> min de leitura</span></div>
        <div class="conteudo"><?= $post['conteudo'] ?></div>
        <div class="interact-bar">
            <button class="btn-act reaction" data-id="<?= $id ?>" data-type="like">👍 <?= (int) $post['qtd_likes'] ?></button>
            <button class="btn-act reaction" data-id="<?= $id ?>" data-type="dislike">👎 <?= (int) $post['qtd_dislikes'] ?></button>
            <button class="btn-act" type="button" onclick="sharePost(<?= $id ?>, '<?= e($post['titulo']) ?>')">Compartilhar</button>
        </div>
        <section><h2>Discussão (<?= (int) $post['qtd_coment'] ?>)</h2>
            <?php if (loggedIn()): ?><form method="post" class="comment-form"><textarea name="comment" placeholder="O que essa história te fez sentir?" required></textarea><button class="btn-send">Publicar pensamento</button></form><?php else: ?><p>Para participar, <a href="login.php">faça login</a>.</p><?php endif; ?>
            <?php foreach (renderComments($id) as $comment): ?>
                <div class="comment"><strong><?= e(authorName($comment)) ?></strong> <small><?= date('d/m H:i', strtotime($comment['data_comentario'])) ?><?= $comment['editado'] ? ' · editado' : '' ?></small>
                    <div class="comment-text"><?= nl2br(e($comment['comentario'])) ?></div>
                    <?php if (loggedIn() && ($_SESSION['user_login'] === $comment['usuario'] || $_SESSION['user_level'] === 'admin')): ?>
                        <form method="post"><input type="hidden" name="delete_comment" value="<?= (int) $comment['id'] ?>"><button class="btn-mini" onclick="return confirm('Excluir este comentário?')">Excluir</button></form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </section>
    </article>
</main>
<?php renderEnd(); ?>
