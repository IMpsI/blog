<?php
declare(strict_types=1);

$root = dirname(__DIR__);

function source(string $relative): string
{
    $content = file_get_contents(dirname(__DIR__) . '/' . $relative);
    if ($content === false) {
        throw new RuntimeException("Arquivo não encontrado: {$relative}");
    }
    return $content;
}

function expectTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$index = source('index.php');
$read = source('read.php');
$javascript = source('src/js/app.js');
$editor = source('editor.php');
$users = source('users.php');

expectTrue(str_contains($index, 'e($post[\'conteudo\'])'), 'Conteúdo dos posts precisa ser escapado na página inicial.');
expectTrue(str_contains($read, 'e($post[\'conteudo\'])'), 'Conteúdo dos posts precisa ser escapado na leitura.');
expectTrue(!str_contains($javascript, 'innerHTML'), 'Busca não pode inserir resultados usando innerHTML.');
expectTrue(str_contains($javascript, 'textContent'), 'Busca precisa usar textContent para títulos.');
expectTrue(str_contains($index, 'JSON_HEX_TAG'), 'Título usado no compartilhamento precisa ser codificado para JavaScript.');
expectTrue(str_contains($read, 'JSON_HEX_TAG'), 'Título usado no compartilhamento precisa ser codificado para JavaScript.');
expectTrue(str_contains($editor, 'requireEditor();'), 'Editor precisa exigir permissão de autor ou administrador.');
expectTrue(str_contains($users, 'requireAdmin();'), 'Gerenciamento de usuários precisa exigir administrador.');
expectTrue(str_contains(source('delete_user.php'), 'requireAdmin();'), 'Exclusão de usuários precisa exigir administrador.');
expectTrue(str_contains(source('delete_post.php'), 'requireEditor();'), 'Exclusão de posts precisa exigir editor.');
expectTrue(str_contains(source('reaction.php'), 'http_response_code(401)'), 'Reações precisam rejeitar visitantes.');

echo "Testes de segurança passaram.\n";
