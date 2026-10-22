<?php
require_once __DIR__ . '/bootstrap.php';
requireAdmin();
$user = dbOne('SELECT usuario FROM usuarios WHERE id = ?', [(int) ($_GET['id'] ?? 0)]);
if ($user && $user['usuario'] !== $_SESSION['user_login']) dbExec('DELETE FROM usuarios WHERE id = ?', [(int) $_GET['id']]);
redirect('users.php');
