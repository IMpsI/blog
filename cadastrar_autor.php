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
        $mensagem = "<p style='color: #4CAF50;'>Autor '$nome' cadastrado!</p>";
    } else {
        $mensagem = "<p style='color: #F44336;'>Erro: Usuário já existe.</p>";
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
    <link rel="stylesheet" href="src/css/style.css">
    <link rel="stylesheet" href="src/css/painel.css">
    <style>
        body {
            background: #121212;
            color: #e0e0e0;
            font-family: sans-serif;
            padding: 40px;
        }

        .flex-container {
            display: flex;
            gap: 40px;
            justify-content: center;
            align-items: flex-start;
        }

        .box {
            background: #1e1e1e;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .form-box {
            width: 350px;
        }

        .list-box {
            flex-grow: 1;
            max-width: 600px;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            background: #2d2d2d;
            border: 1px solid #444;
            color: white;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #2196F3;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #333;
        }

        th {
            color: #888;
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        .btn-edit {
            color: #2196F3;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .btn-voltar {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #888;
            text-decoration: none;
        }

        .badge {
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 0.7rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-admin {
            background: #f44336;
            color: white;
        }

        .badge-autor {
            background: #4caf50;
            color: white;
        }
    </style>
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