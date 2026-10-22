<?php
require_once __DIR__ . '/bootstrap.php';
requireEditor();
$isAdmin = $_SESSION['user_level'] === 'admin';
$posts = $isAdmin ? postQuery() : postQuery('WHERE p.autor_login = ?', [$_SESSION['user_login']]);
renderStart('Painel');
?>
<main class="container">
    <div class="dashboard-grid">
        <aside class="sidebar"><a class="button button-primary" href="editor.php">Nova
                história</a><?php if ($isAdmin): ?><a class="button" href="users.php">Gerenciar
                    contas</a><?php endif; ?><a class="button" href="profile.php">Meu perfil</a></aside>
        <section class="content-box">
            <h1>Publicações recentes</h1>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody><?php foreach ($posts as $post): ?><tr>
                                <td><?= e($post['titulo']) ?></td>
                                <td><?= date('d/m/Y', strtotime($post['data_publicacao'])) ?></td>
                                <td><a href="editor.php?id=<?= (int) $post['id'] ?>">Editar</a> · <a
                                        href="delete_post.php?id=<?= (int) $post['id'] ?>"
                                        onclick="return confirm('Excluir permanentemente?')">Excluir</a></td>
                            </tr><?php endforeach; ?></tbody>
                </table>
            </div>
        </section>
    </div>
</main>
<?php renderEnd(); ?>