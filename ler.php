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
    echo "<h2 style='text-align:center; margin-top:50px; color:#2C3E50;'>História não encontrada.</h2>";
    exit;
}

$autor = $p['nome_artistico'] ?: ($p['nome'] ?: 'Anônimo');
$time = max(1, ceil(str_word_count(strip_tags($p['conteudo'])) / 200));

$pageTitle = htmlspecialchars($p['titulo']) . ' - O Blog';
$busca = '';

require 'layout/head.php';
require 'layout/header_pesquisa.php';
?>
<style>
        :root {
            --bg: #F0F8FF;
            --paper: #FFFFFF;
            --ink: #2C3E50;
            --ink-light: #5D6D7E;
            --baby-blue: #85C1E9;
            --accent: #3498DB;
            --border: #EBF5FB;
        }

        body.dark-mode {
            --bg: #0B1622;
            --paper: #15202B;
            --ink: #E1E8ED;
            --ink-light: #8899A6;
            --baby-blue: #1B91DA;
            --accent: #3498DB;
            --border: #1E2F3F;
        }

        body {
            background: var(--bg);
            color: var(--ink);
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            transition: 0.3s;
        }

        header {
            background: var(--paper);
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 2px solid var(--border);
        }

        .logo {
            font-size: 1.6rem;
            font-weight: bold;
            color: var(--accent);
            text-decoration: none;
            font-family: 'Georgia', serif;
            font-style: italic;
        }

        .nav-links {
            display: flex;
            align-items: center;
        }

        .nav-links a {
            color: var(--ink-light);
            text-decoration: none;
            margin-left: 15px;
            font-weight: 600;
            padding: 8px 15px;
            border-radius: 20px;
            transition: 0.2s;
        }

        .nav-links a:hover {
            color: var(--accent);
            background: var(--bg);
        }

        .btn-dark {
            background: none;
            border: 1px solid var(--baby-blue);
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            margin-left: 10px;
            color: var(--ink);
            line-height: 0;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .post {
            background: var(--paper);
            padding: 50px 40px;
            border-radius: 15px;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border);
        }

        h1 {
            margin: 0 0 20px 0;
            font-family: 'Georgia', serif;
            font-size: 2.8rem;
            color: var(--ink);
            text-align: center;
            line-height: 1.1;
        }

        .meta {
            font-size: 1rem;
            color: var(--ink-light);
            display: flex;
            justify-content: center;
            gap: 20px;
            border-bottom: 1px dashed var(--baby-blue);
            padding-bottom: 20px;
            margin-bottom: 40px;
        }

        .conteudo {
            line-height: 1.9;
            font-family: 'Georgia', serif;
            font-size: 1.25rem;
            color: var(--ink);
        }

        .conteudo p {
            margin-bottom: 25px;
        }

        .interact-bar {
            display: flex;
            justify-content: center;
            gap: 15px;
            background: var(--bg);
            padding: 15px;
            border-radius: 12px;
            margin: 40px 0;
        }

        .btn-act {
            background: var(--paper);
            border: 1px solid var(--border);
            color: var(--ink-light);
            padding: 10px 20px;
            border-radius: 30px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            transition: 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-act:hover {
            border-color: var(--baby-blue);
            color: var(--accent);
            transform: translateY(-2px);
        }

        /* SEÇÃO DE COMENTÁRIOS (Aberta) */
        .comments-section {
            margin-top: 50px;
            border-top: 2px solid var(--border);
            padding-top: 40px;
        }

        .comments-section h3 {
            font-family: 'Georgia', serif;
            font-size: 1.8rem;
            margin-bottom: 30px;
            text-align: center;
        }

        .comment {
            background: var(--bg);
            padding: 20px;
            border-radius: 0 20px 20px 20px;
            margin-bottom: 20px;
            border: 1px solid var(--border);
            position: relative;
        }

        .comment strong {
            color: var(--accent);
            font-family: 'Georgia', serif;
            font-size: 1.1rem;
        }

        .comment-text {
            margin-top: 10px;
            color: var(--ink);
            font-size: 1.05rem;
            line-height: 1.6;
        }

        .comment-actions {
            position: absolute;
            top: 15px;
            right: 20px;
            display: flex;
            gap: 12px;
        }

        .btn-mini {
            background: none;
            border: none;
            color: #999;
            font-size: 0.85rem;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-mini:hover {
            color: var(--accent);
        }

        textarea {
            width: 100%;
            padding: 15px;
            border-radius: 12px;
            border: 1px solid var(--baby-blue);
            background: var(--paper);
            color: var(--ink);
            margin: 15px 0;
            resize: vertical;
            box-sizing: border-box;
            font-family: inherit;
            font-size: 1.05rem;
            outline: none;
        }

        .btn-send {
            background: var(--baby-blue);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1rem;
            transition: 0.3s;
            display: block;
            margin: 0 auto;
        }

        .btn-send:hover {
            background: var(--accent);
            transform: translateY(-2px);
        }
    </style>

    <div class="container">
        <article class="post">
            <h1><?= htmlspecialchars($p['titulo']) ?></h1>
            <div class="meta">
                <span>Por <strong><?= htmlspecialchars($autor) ?></strong> • <?= date('d/m/Y', strtotime($p['data_publicacao'])) ?></span>
                <span style="color: var(--accent); font-weight: bold;">⏱️ <?= $time ?> min de leitura</span>
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
                    <form method="POST" style="margin-bottom: 50px;">
                        <textarea name="comentario" placeholder="O que essa história te fez sentir?" required></textarea>
                        <button type="submit" class="btn-send">Publicar Pensamento</button>
                    </form>
                <?php else: ?>
                    <p style="text-align:center; background: var(--bg); padding: 20px; border-radius: 12px; margin-bottom: 40px;">
                        Para participar da discussão, <a href="entrar" style="color:var(--accent); font-weight:bold;">faça login</a> ou <a href="cadastro" style="color:var(--accent); font-weight:bold;">crie sua conta</a>.
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
                            <?php if ($c['editado']) echo "<small style='color:var(--ink-light)'> (editado)</small>"; ?>
                            <span style="font-size:0.8rem; color:var(--ink-light); margin-left:10px;"><?= date('d/m H:i', strtotime($c['data_comentario'])) ?></span>

                            <div class="comment-actions">
                                <?php if ($meu): ?><button class="btn-mini" onclick="editCom(<?= $c['id'] ?>)">Editar</button><?php endif; ?>
                                <?php if ($meu || $adm): ?>
                                    <form method="POST" style="display:inline" onsubmit="return confirm('Excluir este comentário?')">
                                        <input type="hidden" name="excluir_comentario_id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn-mini" style="color:#E74C3C">Excluir</button>
                                    </form>
                                <?php endif; ?>
                            </div>

                            <div class="comment-text" id="ct-<?= $c['id'] ?>"><?= nl2br(htmlspecialchars($c['comentario'])) ?></div>

                            <?php if ($meu): ?>
                                <form method="POST" id="fe-<?= $c['id'] ?>" style="display:none">
                                    <input type="hidden" name="editar_comentario_id" value="<?= $c['id'] ?>">
                                    <textarea name="novo_comentario" required><?= $c['comentario'] ?></textarea>
                                    <div style="text-align:right">
                                        <button type="button" class="btn-send" style="background:#888; display:inline-block; margin-right:10px;" onclick="location.reload()">Cancelar</button>
                                        <button type="submit" class="btn-send" style="display:inline-block;">Salvar</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>

                    <?php if ($total == 0): ?>
                        <p style="text-align:center; color:var(--ink-light); font-style:italic;">Nenhum comentário ainda. Seja o primeiro a quebrar o silêncio!</p>
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