const { test, expect } = require('./fixtures');

test('visitante pesquisa e abre uma história', async ({ page }) => {
    await page.goto('/');
    await expect(page.getByRole('heading', { name: 'História de teste' })).toBeVisible();
    await page.getByPlaceholder('Pesquisar histórias...').fill('História de teste');
    await expect(page.locator('#search-results a')).toContainText('História de teste');
    await page.locator('#search-results a').first().click();
    await expect(page).toHaveURL(/read\.php\?id=\d+/);
    await expect(page.getByText('Conteúdo público para os testes.')).toBeVisible();
});

test('leitor faz login, comenta e reage', async ({ page, users, login }, testInfo) => {
    await login(users.leitor);
    await page.goto('/');
    const post = page.locator('article.post').filter({ hasText: 'História de teste' });
    await post.locator('button.reaction[data-type="like"]').click();
    await expect(post.locator('button.reaction[data-type="like"]')).toHaveText(/👍\s*[1-9]\d*/);
    await post.locator('textarea[name="comment"]').fill(`Comentário criado no teste ${testInfo.project.name}.`);
    await post.getByRole('button', { name: 'Comentar' }).click();
    await expect(page.getByText(`Comentário criado no teste ${testInfo.project.name}`).first()).toBeVisible();
});

test('autor cria e edita uma história', async ({ page, users, login }) => {
    await login(users.autor);
    await page.goto('/editor.php');
    await page.getByPlaceholder('Título da história').fill('História criada no teste');
    await page.getByPlaceholder('Escreva sua história...').fill('Conteúdo criado no teste.');
    await page.getByRole('button', { name: 'Salvar história' }).click();
    await expect(page).toHaveURL(/dashboard\.php/);
    await expect(page.locator('tbody').getByText('História criada no teste').first()).toBeVisible();
});

test('admin acessa gerenciamento e vê os usuários', async ({ page, users, login }) => {
    await login(users.admin);
    await page.goto('/users.php');
    await expect(page.getByRole('heading', { name: 'Contas' })).toBeVisible();
    await expect(page.getByText('teste_leitor')).toBeVisible();
    await expect(page.getByText('teste_autor')).toBeVisible();
});

test('admin cria um novo autor', async ({ page, users, login }, testInfo) => {
    await login(users.admin);
    await page.goto('/users.php');
    await page.getByPlaceholder('Nome').fill('Autor criado no E2E');
    const username = `autor_e2e_${testInfo.project.name}`;
    await page.getByPlaceholder('Usuário').fill(username);
    await page.getByPlaceholder('Senha').fill('NovoAutor123!');
    await page.getByRole('combobox').selectOption('autor');
    await page.getByRole('button', { name: 'Cadastrar' }).click();
    await expect(page.getByText(username)).toBeVisible();
});

test('confirma uma conta por token', async ({ page, users, login }) => {
    await page.goto('/confirm_email.php?token=0123456789abcdef0123456789abcdef');
    await login(users.pendente);
    await expect(page).toHaveURL(/index\.php/);
});

test('leitor não acessa o painel de autor', async ({ page, users, login }) => {
    await login(users.leitor);
    await page.goto('/dashboard.php');
    await expect(page).toHaveURL(/index\.php/);
});

test('tema e layout funcionam no viewport mobile', async ({ page }) => {
    await page.goto('/');
    await page.locator('#themeBtn').click();
    await expect(page.locator('body')).toHaveClass(/dark-mode/);
    await expect(page.locator('.site-header')).toBeVisible();
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBeTruthy();
});
