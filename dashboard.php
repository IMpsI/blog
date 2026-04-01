<?php
require_once __DIR__ . '/bootstrap.php';


if (!isset($_SESSION['logado']) || $_SESSION['nivel'] === 'leitor') {
    session_destroy();
    header('Location: index.php');
    exit;
}

$user_login = $_SESSION['usuario_login'];
$is_admin = ($_SESSION['nivel'] === 'admin');

$sql = $is_admin ? "SELECT posts.*, usuarios.nome_artistico FROM posts LEFT JOIN usuarios ON posts.autor_login = usuarios.usuario ORDER BY data_publicacao DESC LIMIT 15" : "SELECT * FROM posts WHERE autor_login = :u ORDER BY data_publicacao DESC";
$stmt = $db->prepare($sql);
if (!$is_admin) $stmt->bindValue(':u', $user_login, SQLITE3_TEXT);
$posts = $stmt->execute();
?>

<?php
require 'layout/head.php';
require 'layout/header_pesquisa.php';
?>

<div class="container-painel">
    <div class="sidebar">
        <a href="estudio" class="nav-btn btn-new">Nova História</a>
        <?php if ($is_admin): ?>
            <a href="editar_autor.php" class="nav-btn btn-accounts">Gerenciamento de Contas</a>
        <?php endif; ?>
        <a href="minha-conta" class="nav-btn btn-profile">Meu Perfil</a>
    </div>

    <div class="main-content">
        <h2 class="dashboard-title">Publicações Recentes</h2>
        <table>
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Data</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($p = $posts->fetchArray(SQLITE3_ASSOC)): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['titulo']) ?></td>
                        <td class="dashboard-date"><?= date('d/m/y', strtotime($p['data_publicacao'])) ?></td>
                        <td>
                            <a href="editar-post?id=<?= $p['id'] ?>" class="action-link edit">Editar</a>
                            <a href="excluir-post?id=<?= $p['id'] ?>" class="action-link del" onclick="return confirm('Excluir permanentemente?')">Excluir</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    if (localStorage.getItem('theme') === 'dark') document.body.classList.add('dark-mode');
    document.getElementById('themeBtn').addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
        localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
    });
</script>
</body>

</html>