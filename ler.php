<?php
require_once __DIR__ . '/bootstrap.php';

$post_id = $_GET['id'] ?? null;
if (!$post_id) {
    header('Location: index.php');
    exit;
}

// --- 1. PROCESSAMENTO DE AÇÕES (POST) ---
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
        $stmt->bindValue(':p', $post_id, SQLITE3_INTEGER);
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
    header("Location: ler?id=" . $post_id);
    exit;
}

// --- 2. BUSCA DO POST ESPECÍFICO ---
$query = "SELECT posts.*, usuarios.nome_artistico, usuarios.nome,
        (SELECT COUNT(*) FROM comentarios WHERE post_id = posts.id) AS qtd_coment,
        (SELECT COUNT(*) FROM reacoes WHERE post_id = posts.id AND tipo_reacao = 'like') AS qtd_likes,
        (SELECT COUNT(*) FROM reacoes WHERE post_id = posts.id AND tipo_reacao = 'dislike') AS qtd_dislikes
        FROM posts LEFT JOIN usuarios ON posts.autor_login = usuarios.usuario 
        WHERE posts.id = :id";

$stmt = $db->prepare($query);
$stmt->bindValue(':id', $post_id, SQLITE3_INTEGER);
$p = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if (!$p) {
    echo "<h2 class='not-found'>História não encontrada.</h2>";
    exit;
}

$autor = $p['nome_artistico'] ?: ($p['nome'] ?: 'Anônimo');
$time = max(1, ceil(str_word_count(strip_tags($p['conteudo'])) / 200));

$pageTitle = htmlspecialchars($p['titulo']) . ' - O Blog';
$pageStyles = ['ler.css'];
$busca = '';

require 'layout/head.php';
require 'layout/header_pesquisa.php';
?>
    <div class="container">
        <article class="post">
            <h1><?= htmlspecialchars($p['titulo']) ?></h1>
            <div class="meta">
                <span>Por <strong><?= htmlspecialchars($autor) ?></strong> • <?= date('d/m/Y', strtotime($p['data_publicacao'])) ?></span>
                <span class="read-time">⏱️ <?= $time ?> min de leitura</span>
            </div>

            <div class="conteudo">
                <?= $p['conteudo'] ?>
            </div>

            <div class="interact-bar">
                <button class="btn-act btn-reacao" data-id="<?= $post_id ?>" data-tipo="like">👍 <span id="l-<?= $post_id ?>"><?= $p['qtd_likes'] ?></span></button>
                <button class="btn-act btn-reacao" data-id="<?= $post_id ?>" data-tipo="dislike">👎 <span id="d-<?= $post_id ?>"><?= $p['qtd_dislikes'] ?></span></button>
                <button class="btn-act" onclick="share(<?= $post_id ?>, '<?= addslashes($p['titulo']) ?>')">🔗 Compartilhar</button>
            </div>

            <section class="comments-section">
                <h3>Discussão (<?= $p['qtd_coment'] ?>)</h3>

                <?php if (isset($_SESSION['logado'])): ?>
                    <form method="POST" class="comment-form-spacing">
                        <textarea name="comentario" placeholder="O que essa história te fez sentir?" required></textarea>
                        <button type="submit" class="btn-send">Publicar Pensamento</button>
                    </form>
                <?php else: ?>
                    <p class="comments-login-box">
                        Para participar da discussão, <a href="entrar" class="comments-login-link">faça login</a> ou <a href="cadastro" class="comments-login-link">crie sua conta</a>.
                    </p>
                <?php endif; ?>

                <div class="lista-coments">
                    <?php
                    // LIMITE DE 10 COMENTÁRIOS NA PÁGINA DE LEITURA
                    $coms = $db->query("SELECT comentarios.*, usuarios.nome_artistico, usuarios.nome, usuarios.usuario FROM comentarios LEFT JOIN usuarios ON comentarios.usuario_login = usuarios.usuario WHERE post_id = $post_id ORDER BY data_comentario DESC LIMIT 10");
                    $total = 0;
                    while ($c = $coms->fetchArray(SQLITE3_ASSOC)):
                        $total++;
                        $autor_c = $c['nome_artistico'] ?: ($c['nome'] ?: $c['usuario']);
                        $meu = (isset($_SESSION['logado']) && $_SESSION['usuario_login'] === $c['usuario']);
                        $adm = (isset($_SESSION['logado']) && $_SESSION['nivel'] === 'admin');
                    ?>
                        <div class="comment">
                            <strong><?= htmlspecialchars($autor_c) ?></strong>
                            <?php if ($c['editado']) echo "<small class='comment-edited'> (editado)</small>"; ?>
                            <span class="comment-date"><?= date('d/m H:i', strtotime($c['data_comentario'])) ?></span>

                            <div class="comment-actions">
                                <?php if ($meu): ?><button class="btn-mini" onclick="editCom(<?= $c['id'] ?>)">Editar</button><?php endif; ?>
                                <?php if ($meu || $adm): ?>
                                    <form method="POST" class="inline-form" onsubmit="return confirm('Excluir este comentário?')">
                                        <input type="hidden" name="excluir_comentario_id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn-mini btn-mini-danger">Excluir</button>
                                    </form>
                                <?php endif; ?>
                            </div>

                            <div class="comment-text" id="ct-<?= $c['id'] ?>"><?= nl2br(htmlspecialchars($c['comentario'])) ?></div>

                            <?php if ($meu): ?>
                                <form method="POST" id="fe-<?= $c['id'] ?>" class="edit-form">
                                    <input type="hidden" name="editar_comentario_id" value="<?= $c['id'] ?>">
                                    <textarea name="novo_comentario" required><?= $c['comentario'] ?></textarea>
                                    <div class="edit-actions">
                                        <button type="button" class="btn-send btn-cancel-inline" onclick="location.reload()">Cancelar</button>
                                        <button type="submit" class="btn-send btn-save-inline">Salvar</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>

                    <?php if ($total == 0): ?>
                        <p class="comments-empty">Nenhum comentário ainda. Seja o primeiro a quebrar o silêncio!</p>
                    <?php endif; ?>
                </div>
            </section>
        </article>
    </div>

    <?php require 'layout/script.php'; ?>
    <script>
        // MODO NOTURNO
        const themeBtn = document.getElementById('themeBtn');
        if (localStorage.getItem('theme') === 'dark') document.body.classList.add('dark-mode');
        themeBtn.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
        });

        // REAÇÕES AJAX
        document.querySelectorAll('.btn-reacao').forEach(b => {
            b.addEventListener('click', async () => {
                <?php if (!isset($_SESSION['logado'])) {
                    echo "alert('Entre na sua conta para reagir!'); return;";
                } ?>
                const pid = b.dataset.id,
                    tipo = b.dataset.tipo;
                const res = await fetch('reagir.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        post_id: pid,
                        tipo: tipo
                    })
                });
                const d = await res.json();
                if (d.sucesso) {
                    document.getElementById('l-' + pid).innerText = d.likes;
                    document.getElementById('d-' + pid).innerText = d.dislikes;
                }
            });
        });

        // UTILITÁRIOS
        function editCom(id) {
            document.getElementById('ct-' + id).style.display = 'none';
            document.getElementById('fe-' + id).style.display = 'block';
        }

        function share(id, t) {
            const url = window.location.href;
            if (navigator.share) {
                navigator.share({
                    title: t,
                    url: url
                }).catch(() => {});
            } else {
                navigator.clipboard.writeText(url);
                alert('Link da história copiado!');
            }
        }
    </script>
<?php require 'layout/footer.php'; ?>