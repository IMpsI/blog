<?php
session_start();

// Destrói todas as variáveis da sessão (limpa o crachá)
$_SESSION = array();

// Destrói a sessão no servidor
session_destroy();

// Redireciona para o "apelido" que criamos no .htaccess
header('Location: entrar');
exit;
