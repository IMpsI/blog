<?php
require_once __DIR__ . '/bootstrap.php';

$search = trim($_GET['q'] ?? '');
$posts = postQuery($search === '' ? '' : 'WHERE p.titulo LIKE ? OR p.conteudo LIKE ?', $search === '' ? [] : ["%{$search}%", "%{$search}%"]);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handleComment((int) $_POST['post_id']);
    redirect('index.php#post-' . (int) $_POST['post_id']);
}

renderStart('Biblioteca');
?>
<main class="container">
    <?php if ($search !== ''): ?><h2>Resultados para “<?= e($search) ?>”</h2><?php endif; ?>
    <?php if (!$posts): ?><div class="box">
            <h2>Nenhuma história encontrada.</h2>
        </div><?php endif; ?>
    <?php foreach ($posts as $post): ?>
        <article class="post" id="post-<?= (int) $post['id'] ?>">
            <h2><?= e($post['titulo']) ?></h2>
            <div class="meta"><span>Por <strong><?= e(authorName($post)) ?></strong> ·
                    <?= date('d/m/Y', strtotime($post['data_publicacao'])) ?></span><span><?= max(1, (int) ceil(str_word_count(strip_tags($post['conteudo'])) / 200)) ?>
                    min</span></div>
            <div class="conteudo"><?= e($post['conteudo']) ?></div>
            <div class="interact-bar">
                <button class="btn-act reaction" data-id="<?= (int) $post['id'] ?>" data-type="like">👍
                    <?= (int) $post['qtd_likes'] ?></button>
                <button class="btn-act reaction" data-id="<?= (int) $post['id'] ?>" data-type="dislike">👎
                    <?= (int) $post['qtd_dislikes'] ?></button>
                <a class="button" href="read.php?id=<?= (int) $post['id'] ?>">Ler completo</a>
                <button class="btn-act" type="button"
                    onclick="sharePost(<?= (int) $post['id'] ?>, <?= e(json_encode($post['titulo'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)) ?>)">Compartilhar</button>
            </div>
            <?php if (loggedIn()): ?>
                <form method="post" class="comment-form"><input type="hidden" name="post_id"
                        value="<?= (int) $post['id'] ?>"><textarea name="comment" placeholder="O que você achou?"
                        required></textarea><button class="btn-send">Comentar</button></form>
            <?php else: ?><p>Faça <a href="login.php">login</a> para comentar.</p><?php endif; ?>
            <?php foreach (array_slice(renderComments((int) $post['id'], 3), 0, 3) as $comment): ?>
                <div class="comment">
                    <strong><?= e(authorName($comment)) ?></strong><?= $comment['editado'] ? ' <small>(editado)</small>' : '' ?>
                    <div class="comment-text"><?= nl2br(e($comment['comentario'])) ?></div>
                </div>
            <?php endforeach; ?>
        </article>
    <?php endforeach; ?>
</main>
<?php renderEnd(); ?>