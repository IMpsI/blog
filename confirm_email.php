<?php
require_once __DIR__ . '/bootstrap.php';
$token = trim($_GET['token'] ?? '');
if ($token !== '') dbExec('UPDATE usuarios SET email_confirmado = 1, token_autenticacao = NULL WHERE token_autenticacao = ?', [$token]);
redirect('login.php');
