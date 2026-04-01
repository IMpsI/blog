<?php
require_once __DIR__ . '/bootstrap.php';

if (!isset($_SESSION['logado']) || $_SESSION['nivel'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Lógica de Exclusão (Admin não pode se excluir)
if (isset($_GET['excluir'])) {
    $id_excluir = $_GET['excluir'];
    $stmt_check = $db->prepare("SELECT usuario FROM usuarios WHERE id = :id");
    $stmt_check->bindValue(':id', $id_excluir, SQLITE3_INTEGER);
    $user_alvo = $stmt_check->execute()->fetchArray(SQLITE3_ASSOC);

    if ($user_alvo && $user_alvo['usuario'] !== $_SESSION['usuario_login']) {
        $stmt_del = $db->prepare("DELETE FROM usuarios WHERE id = :id");
        $stmt_del->bindValue(':id', $id_excluir, SQLITE3_INTEGER);
        $stmt_del->execute();
    }
    header('Location: editar_autor.php');
    exit;
}

$usuarios = $db->query("SELECT * FROM usuarios ORDER BY nivel DESC, nome ASC");
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Contas - O Blog</title>
    <link rel="stylesheet" href="src/css/header.css">
    <link rel="stylesheet" href="src/css/style.css">
    <link rel="stylesheet" href="src/css/painel.css">
    <link rel="stylesheet" href="src/css/editar_autor.css">
</head>

<body>
    <?php $busca = ''; require 'layout/header_pesquisa.php'; ?>

    <div class="container">
        <div class="content-box">
            <h2 class="title-users">Usuários do Sistema</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nome / Usuário</th>
                        <th>Nível</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($u = $usuarios->fetchArray(SQLITE3_ASSOC)): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($u['nome'] ?? '') ?></strong><br>
                                <small class="user-handle">@<?= htmlspecialchars($u['usuario'] ?? '') ?></small>
                            </td>
                            <td>
                                <?php
                                $lvl = $u['nivel'];
                                echo "<span class='badge badge-$lvl'>$lvl</span>";
                                ?>
                            </td>
                            <td>
                                <?php if ($u['usuario'] !== $_SESSION['usuario_login']): ?>
                                    <a href="?excluir=<?= $u['id'] ?>" class="del-link" onclick="return confirm('Remover esta conta permanentemente?')">Excluir</a>
                                <?php else: ?>
                                    <small class="self-account">Sua conta</small>
                                <?php endif; ?>
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