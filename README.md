# OBlog

## Padronização do Projeto

### Bootstrap único
- Todas as páginas PHP devem usar `require_once __DIR__ . '/bootstrap.php';` no topo.
- O bootstrap centraliza sessão e conexão com banco.

### Banco de dados centralizado
- O arquivo `db.php` concentra criação de tabelas e migrações compatíveis.
- Evite executar `ALTER TABLE` espalhado em páginas de rota.

### Layout desacoplado
- `layout/head.php` deve conter apenas estrutura HTML de cabeçalho e imports de CSS.
- Consultas SQL e preparação de dados devem ficar no arquivo da página (exemplo: `index.php`).

### Padrão de estilo
- O projeto usa `.editorconfig` para manter consistência de indentação, final de linha e charset.