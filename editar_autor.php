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
    <link rel="stylesheet" href="src/css/style.css">
    <link rel="stylesheet" href="src/css/painel.css">
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
            min-height: 100vh;
        }

        header {
            background: var(--paper);
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .btn-theme {
            background: none;
            border: 1px solid var(--baby-blue);
            border-radius: 20px;
            cursor: pointer;
            padding: 6px 15px;
            color: var(--ink);
            font-size: 0.8rem;
            font-weight: bold;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .content-box {
            background: var(--paper);
            padding: 30px;
            border-radius: 15px;
            border: 1px solid var(--border);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            text-align: left;
            padding: 15px;
            color: var(--ink-light);
            font-size: 0.85rem;
            text-transform: uppercase;
            border-bottom: 2px solid var(--border);
        }

        td {
            padding: 15px;
            border-bottom: 1px solid var(--border);
        }

        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-admin {
            background: #FAD7A0;
            color: #7E5109;
        }

        .badge-autor {
            background: #AED6F1;
            color: #21618C;
        }

        .badge-leitor {
            background: #D5DBDB;
            color: #515A5A;
        }

        .del-link {
            color: #E74C3C;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.85rem;
            border: 1px solid #E74C3C;
            padding: 5px 10px;
            border-radius: 15px;
            transition: 0.3s;
        }

        .del-link:hover {
            background: #E74C3C;
            color: white;
        }
    </style>
</head>

<body>
    <?php $busca = ''; require 'layout/header_pesquisa.php'; ?>

    <div class="container">
        <div class="content-box">
            <h2 style="margin-top:0; font-family:'Georgia', serif; font-weight: normal;">Usuários do Sistema</h2>
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
                                <small style="color:var(--ink-light)">@<?= htmlspecialchars($u['usuario'] ?? '') ?></small>
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
                                    <small style="color:var(--ink-light); font-style:italic;">Sua conta</small>
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