<?php
require_once __DIR__ . '/bootstrap.php';

$busca = trim($_GET['q'] ?? '');

$sql = "SELECT posts.*, usuarios.nome_artistico, usuarios.nome,
        (SELECT COUNT(*) FROM comentarios WHERE post_id = posts.id) AS qtd_coment,
        (SELECT COUNT(*) FROM reacoes WHERE post_id = posts.id AND tipo_reacao = 'like') AS qtd_likes,
        (SELECT COUNT(*) FROM reacoes WHERE post_id = posts.id AND tipo_reacao = 'dislike') AS qtd_dislikes
        FROM posts
        LEFT JOIN usuarios ON posts.autor_login = usuarios.usuario";

if ($busca !== '') {
    $sql .= " WHERE posts.titulo LIKE :b OR posts.conteudo LIKE :b";
}

$sql .= " ORDER BY posts.data_publicacao DESC";

$stmt = $db->prepare($sql);
if ($busca !== '') {
    $stmt->bindValue(':b', "%{$busca}%", SQLITE3_TEXT);
}
$posts = $stmt->execute();

// --- 2. PROCESSAMENTO DE AÇÕES (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['logado'])) exit;

    // Excluir Comentário
    if (isset($_POST['excluir_comentario_id'])) {
        $id = $_POST['excluir_comentario_id'];
        $stmt = $db->prepare("SELECT usuario_login FROM comentarios WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $res = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        if ($res && ($_SESSION['nivel'] === 'admin' || $_SESSION['usuario_login'] === $res['usuario_login'])) {
            $db->exec("DELETE FROM comentarios WHERE id = $id");
        }
    }
    // Novo Comentário
    if (isset($_POST['comentario']) && !isset($_POST['editar_comentario_id'])) {
        $stmt = $db->prepare("INSERT INTO comentarios (post_id, usuario_login, comentario) VALUES (:p, :u, :c)");
        $stmt->bindValue(':p', $_POST['post_id'], SQLITE3_INTEGER);
        $stmt->bindValue(':u', $_SESSION['usuario_login'], SQLITE3_TEXT);
        $stmt->bindValue(':c', htmlspecialchars($_POST['comentario']));
        $stmt->execute();
    }
    // Editar Comentário
    if (isset($_POST['editar_comentario_id'])) {
        $stmt = $db->prepare("UPDATE comentarios SET comentario = :c, editado = 1 WHERE id = :id AND usuario_login = :u");
        $stmt->bindValue(':c', htmlspecialchars($_POST['novo_comentario']));
        $stmt->bindValue(':id', $_POST['editar_comentario_id'], SQLITE3_INTEGER);
        $stmt->bindValue(':u', $_SESSION['usuario_login'], SQLITE3_TEXT);
        $stmt->execute();
    }
    header("Location: index.php#post-" . ($_POST['post_id'] ?? $_POST['post_id_origem']));
    exit;
}

$pageTitle = 'O Blog - Biblioteca Digital';
require 'layout/head.php';
require 'layout/header_pesquisa.php';
?>

<div class="container">
    <?php if ($busca): ?>
        <h3 class="search-result-title">Mostrando resultados para: "<strong><?= htmlspecialchars($busca) ?></strong>"</h3>
    <?php endif; ?>

    <?php
    $tem_posts = false;
    while ($p = $posts->fetchArray(SQLITE3_ASSOC)):
        $tem_posts = true;
        $pid = $p['id'];
        $time = max(1, ceil(str_word_count(strip_tags($p['conteudo'])) / 200));
        $autor = $p['nome_artistico'] ?: ($p['nome'] ?: 'Anônimo');
    ?>
        <article class="post" id="post-<?= $pid ?>">
            <h2><?= htmlspecialchars($p['titulo']) ?></h2>
            <div class="meta">
                <span>Por <strong><?= htmlspecialchars($autor) ?></strong> • <?= date('d/m/Y', strtotime($p['data_publicacao'])) ?></span>
                <span class="read-time-pill">⏱️ <?= $time ?> min</span>
            </div>
            <div class="conteudo"><?= $p['conteudo'] ?></div>

            <div class="interact-bar">
                <button class="btn-act btn-reacao" data-id="<?= $pid ?>" data-tipo="like">👍 <span id="l-<?= $pid ?>"><?= $p['qtd_likes'] ?></span></button>
                <button class="btn-act btn-reacao" data-id="<?= $pid ?>" data-tipo="dislike">👎 <span id="d-<?= $pid ?>"><?= $p['qtd_dislikes'] ?></span></button>
                <button class="btn-act" onclick="toggleComent(<?= $pid ?>)">💬 <?= $p['qtd_coment'] ?> Comentários</button>
                <button class="btn-act" onclick="share(<?= $pid ?>, '<?= addslashes($p['titulo']) ?>')">🔗 Compartilhar</button>
                <a href="ler?id=<?= $pid ?>" class="btn-act btn-read">📖 Ler Completo</a>
            </div>

            <div class="comments-box" id="cb-<?= $pid ?>">
                <div class="lista-coments">
                    <?php
                    // LIMITE DE 3 COMENTÁRIOS NA HOME
                    $coms = $db->query("SELECT comentarios.*, usuarios.nome_artistico, usuarios.nome, usuarios.usuario FROM comentarios LEFT JOIN usuarios ON comentarios.usuario_login = usuarios.usuario WHERE post_id = $pid ORDER BY data_comentario ASC LIMIT 3");
                    $total_exibidos = 0;
                    while ($c = $coms->fetchArray(SQLITE3_ASSOC)):
                        $total_exibidos++;
                        $autor_c = $c['nome_artistico'] ?: ($c['nome'] ?: $c['usuario']);
                        $meu = (isset($_SESSION['logado']) && $_SESSION['usuario_login'] === $c['usuario']);
                        $adm = (isset($_SESSION['logado']) && $_SESSION['nivel'] === 'admin');
                    ?>
                        <div class="comment">
                            <strong><?= htmlspecialchars($autor_c) ?></strong>
                            <?php if ($c['editado']) echo "<small class='comment-edited'> (editado)</small>"; ?>

                            <div class="comment-actions">
                                <?php if ($meu): ?><button class="btn-mini" onclick="editCom(<?= $c['id'] ?>)">Editar</button><?php endif; ?>
                                <?php if ($meu || $adm): ?>
                                    <form method="POST" class="inline-form" onsubmit="return confirm('Excluir este comentário?')">
                                        <input type="hidden" name="excluir_comentario_id" value="<?= $c['id'] ?>">
                                        <input type="hidden" name="post_id_origem" value="<?= $pid ?>">
                                        <button type="submit" class="btn-mini btn-mini-danger">Excluir</button>
                                    </form>
                                <?php endif; ?>
                            </div>

                            <div class="comment-text" id="ct-<?= $c['id'] ?>"><?= nl2br(htmlspecialchars($c['comentario'])) ?></div>

                            <?php if ($meu): ?>
                                <form method="POST" id="fe-<?= $c['id'] ?>" class="edit-form">
                                    <input type="hidden" name="editar_comentario_id" value="<?= $c['id'] ?>">
                                    <input type="hidden" name="post_id_origem" value="<?= $pid ?>">
                                    <textarea name="novo_comentario" required><?= $c['comentario'] ?></textarea>
                                    <button type="submit" class="btn-send">Salvar Alteração</button>
                                    <button type="button" class="btn-send btn-send-cancel" onclick="location.reload()">Cancelar</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>

                <?php if ($p['qtd_coment'] > 3): ?>
                    <a href="ler?id=<?= $pid ?>" class="comments-link-all">Ver todos os <?= $p['qtd_coment'] ?> comentários...</a>
                <?php elseif ($total_exibidos == 0): ?>
                    <p class="comments-empty">Nenhuma resposta ainda. Comece a conversa!</p>
                <?php endif; ?>

                <?php if (isset($_SESSION['logado'])): ?>
                    <form method="POST" class="comment-form-spacing-sm">
                        <input type="hidden" name="post_id" value="<?= $pid ?>">
                        <textarea name="comentario" placeholder="O que você achou dessa história?" required></textarea>
                        <button type="submit" class="btn-send">Publicar Comentário</button>
                    </form>
                <?php else: ?>
                    <p class="comments-login">Faça <a href="entrar" class="comments-login-link">login</a> para comentar.</p>
                <?php endif; ?>
            </div>
        </article>
    <?php endwhile;
    if (!$tem_posts) echo "<div class='empty-posts'><h3>Nenhuma história encontrada aqui.</h3><a href='/' class='empty-posts-link'>Voltar ao início</a></div>";
    ?>
</div>
<?php
require 'layout/script.php';
require 'layout/footer.php';
?>