<?php
require_once __DIR__ . '/bootstrap.php';

if (!isset($_SESSION['logado'])) {
    header('Location: entrar');
    exit;
}

$user_login = $_SESSION['usuario_login'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = htmlspecialchars($_POST['nome'] ?? '');
    $nome_artistico = htmlspecialchars($_POST['nome_artistico'] ?? '');

    $stmt = $db->prepare("UPDATE usuarios SET nome = :n, nome_artistico = :a WHERE usuario = :u");
    $stmt->bindValue(':n', $nome, SQLITE3_TEXT);
    $stmt->bindValue(':a', $nome_artistico, SQLITE3_TEXT);
    $stmt->bindValue(':u', $user_login, SQLITE3_TEXT);

    if ($stmt->execute()) {
        $_SESSION['autor_nome'] = $nome_artistico ?: $nome;
        $msg = "<p class='msg-success'>Perfil atualizado com sucesso!</p>";
    }
}

$u = $db->query("SELECT * FROM usuarios WHERE usuario = '$user_login'")->fetchArray(SQLITE3_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Meu Perfil - O Blog</title>
    <link rel="stylesheet" href="src/css/header.css">
    <link rel="stylesheet" href="src/css/style.css">
    <link rel="stylesheet" href="src/css/painel.css">
    <link rel="stylesheet" href="src/css/minha_conta.css">
</head>

<body>
    <?php $busca = ''; require 'layout/header_pesquisa.php'; ?>

    <div class="container">
        <div class="profile-box">
            <h2>Configurações de Perfil</h2>
            <?= $msg ?>
            <form method="POST">
                <label>Nome Completo</label>
                <input type="text" name="nome" value="<?= htmlspecialchars($u['nome'] ?? '') ?>" required>

                <label>Nome Artístico (Nos posts)</label>
                <input type="text" name="nome_artistico" value="<?= htmlspecialchars($u['nome_artistico'] ?? '') ?>">

                <label>E-mail (Privado)</label>
                <input type="text" value="<?= htmlspecialchars($u['email'] ?? '') ?>" disabled class="input-disabled">

                <button type="submit" class="btn-save">Salvar Alterações</button>
            </form>
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