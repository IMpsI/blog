<?php
require_once __DIR__ . '/bootstrap.php';

if (!isset($_SESSION['logado']) || $_SESSION['nivel'] !== 'admin') {
    header('Location: entrar');
    exit;
}

$mensagem = '';

// Lógica de Cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar'])) {
    $nome = $_POST['nome'];
    $usuario = $_POST['usuario'];
    $nivel_escolhido = $_POST['nivel'];
    $senha_hash = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    $stmt = $db->prepare('INSERT INTO usuarios (nome, usuario, senha, nivel) VALUES (:nome, :user, :pass, :nivel)');
    $stmt->bindValue(':nome', $nome, SQLITE3_TEXT);
    $stmt->bindValue(':user', $usuario, SQLITE3_TEXT);
    $stmt->bindValue(':pass', $senha_hash, SQLITE3_TEXT);
    $stmt->bindValue(':nivel', $nivel_escolhido, SQLITE3_TEXT);

    if (@$stmt->execute()) {
        $mensagem = "<p class='msg-success'>Autor '$nome' cadastrado!</p>";
    } else {
        $mensagem = "<p class='msg-error'>Erro: Usuário já existe.</p>";
    }
}

// Busca a lista de autores para a tabela
$usuarios = $db->query("SELECT id, nome, usuario, nivel FROM usuarios ORDER BY nome ASC");
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Autores</title>
    <link rel="stylesheet" href="src/css/header.css">
    <link rel="stylesheet" href="src/css/style.css">
    <link rel="stylesheet" href="src/css/painel.css">
    <link rel="stylesheet" href="src/css/cadastrar_autor.css">
</head>

<body>
    <?php $busca = ''; require 'layout/header_pesquisa.php'; ?>

    <div class="flex-container">
        <div class="box form-box">
            <h2>Novo Editor</h2>
            <?= $mensagem ?>
            <form method="POST">
                <input type="hidden" name="cadastrar" value="1">
                <input type="text" name="nome" placeholder="Nome" required>
                <input type="text" name="usuario" placeholder="Login" required>
                <input type="password" name="senha" placeholder="Senha" required>
                <select name="nivel">
                    <option value="autor">Autor</option>
                    <option value="admin">Administrador</option>
                </select>
                <button type="submit">Cadastrar</button>
            </form>
            <a href="painel" class="btn-voltar">← Voltar</a>
        </div>

        <div class="box list-box">
            <h2>Autores Atuais</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Usuário</th>
                        <th>Nível</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $usuarios->fetchArray(SQLITE3_ASSOC)): ?>
                        <tr>
                            <td><?= $user['nome'] ?></td>
                            <td><?= $user['usuario'] ?></td>
                            <td>
                                <span class="badge <?= $user['nivel'] == 'admin' ? 'badge-admin' : 'badge-autor' ?>">
                                    <?= $user['nivel'] ?>
                                </span>
                            </td>
                            <td>
                                <a href="editar-autor?id=<?= $user['id'] ?>" class="btn-edit">Editar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>