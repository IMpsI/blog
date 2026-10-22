<?php
require_once __DIR__ . '/bootstrap.php';
requireAdmin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    dbExec('INSERT INTO usuarios (nome, usuario, senha, nivel, email_confirmado) VALUES (?, ?, ?, ?, 1)', [trim($_POST['nome']), trim($_POST['usuario']), password_hash($_POST['senha'], PASSWORD_DEFAULT), $_POST['nivel']]);
}
$users = dbAll('SELECT id, nome, usuario, nivel FROM usuarios ORDER BY nome');
renderStart('Gerenciar contas');
?>
<main class="container">
    <div class="dashboard-grid">
        <section class="box">
            <h1>Novo autor</h1>
            <form method="post" class="form-grid"><input name="nome" placeholder="Nome" required><input name="usuario"
                    placeholder="Usuário" required><input type="password" name="senha" placeholder="Senha"
                    required><select name="nivel">
                    <option value="autor">Autor</option>
                    <option value="admin">Administrador</option>
                </select><button class="btn-submit">Cadastrar</button></form>
        </section>
        <section class="content-box">
            <h1>Contas</h1>
            <div class="table-wrap">
                <table>
                    <tr>
                        <th>Nome</th>
                        <th>Usuário</th>
                        <th>Nível</th>
                        <th></th>
                    </tr><?php foreach ($users as $user): ?><tr>
                            <td><?= e($user['nome']) ?></td>
                            <td><?= e($user['usuario']) ?></td>
                            <td><?= e($user['nivel']) ?></td>
                            <td><?php if ($user['usuario'] !== $_SESSION['user_login']): ?><a
                                        href="delete_user.php?id=<?= (int) $user['id'] ?>"
                                        onclick="return confirm('Remover esta conta?')">Excluir</a><?php endif; ?></td>
                        </tr><?php endforeach; ?>
                </table>
            </div>
        </section>
    </div>
</main>
<?php renderEnd(); ?>