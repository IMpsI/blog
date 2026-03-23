<?php
require 'db.php';

// Limpa a tabela para garantir
$db->exec("DELETE FROM usuarios WHERE usuario = 'Mps'");

$nome = "Matheus";
$user = "Mps";
$pass = password_hash("36574789", PASSWORD_DEFAULT);
$nivel = "admin";

$stmt = $db->prepare('INSERT INTO usuarios (nome, usuario, senha, nivel) VALUES (:n, :u, :p, :l)');
$stmt->bindValue(':n', $nome, SQLITE3_TEXT);
$stmt->bindValue(':u', $user, SQLITE3_TEXT);
$stmt->bindValue(':p', $pass, SQLITE3_TEXT);
$stmt->bindValue(':l', $nivel, SQLITE3_TEXT);

if ($stmt->execute()) {
    echo "Usuário Mps criado com sucesso! Tente logar agora.";
}
