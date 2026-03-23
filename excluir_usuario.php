<?php
require_once __DIR__ . '/bootstrap.php';

// 1 e 2. TRAVA DE SEGURANÇA MÁXIMA: Só Admin passa daqui
if (!isset($_SESSION['logado']) || $_SESSION['nivel'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$id_para_apagar = $_GET['id'] ?? null;

if ($id_para_apagar) {
    // Busca o usuário que vai ser apagado para checar quem é
    $stmt_check = $db->prepare('SELECT usuario FROM usuarios WHERE id = :id');
    $stmt_check->bindValue(':id', $id_para_apagar, SQLITE3_INTEGER);
    $user_alvo = $stmt_check->execute()->fetchArray(SQLITE3_ASSOC);

    if ($user_alvo) {
        // 3. TRAVA ANTISUICÍDIO: O Admin não pode apagar a si mesmo
        if ($user_alvo['usuario'] === $_SESSION['usuario_login']) {
            echo "<script>
                    alert('Operação bloqueada: Você não pode excluir a sua própria conta de Administrador!'); 
                    window.location.href = 'novo-autor';
                  </script>";
            exit;
        } else {
            // Se for outro usuário, passa a faca e deleta
            $stmt_del = $db->prepare('DELETE FROM usuarios WHERE id = :id');
            $stmt_del->bindValue(':id', $id_para_apagar, SQLITE3_INTEGER);
            $stmt_del->execute();
        }
    }
}

// Depois de apagar, recarrega a página de gerenciamento de usuários
header('Location: novo-autor');
exit;
