# O Blog 2.0

Reimplementação em PHP, CSS e JavaScript, com conexão PDO/MySQL e layout compartilhado.

## Instalação

1. Inicie o MySQL no XAMPP.
2. Confira `config.php` ou defina `BLOG_DB_HOST`, `BLOG_DB_PORT`, `BLOG_DB_NAME`, `BLOG_DB_USER` e `BLOG_DB_PASS`.
3. Execute `http://localhost/blog%202.0/setup.php` uma vez.
4. Para reaproveitar os dados da versão antiga, execute `php migrate_sqlite.php`.
5. Remova ou proteja `setup.php` e `migrate_sqlite.php` depois da configuração.

A confirmação de e-mail é opcional e fica desativada por padrão. Para exigir confirmação, defina `BLOG_REQUIRE_EMAIL_CONFIRMATION=1` e configure `BLOG_SMTP_HOST`, `BLOG_SMTP_PORT`, `BLOG_SMTP_USER`, `BLOG_SMTP_PASS` e `BLOG_SMTP_FROM`.
