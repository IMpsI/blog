<?php
require_once __DIR__ . '/bootstrap.php';
requireEditor();
$post = dbOne('SELECT autor_login FROM posts WHERE id = ?', [(int) ($_GET['id'] ?? 0)]);
if ($post && ($_SESSION['user_level'] === 'admin' || $post['autor_login'] === $_SESSION['user_login'])) dbExec('DELETE FROM posts WHERE id = ?', [(int) $_GET['id']]);
redirect('dashboard.php');
