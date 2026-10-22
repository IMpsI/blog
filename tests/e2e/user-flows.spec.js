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

test('login mantém alinhamento simétrico e links do cabeçalho consistentes', async ({ page }) => {
    await page.goto('/login.php');
    const authBox = page.locator('.auth-box');
    const form = authBox.locator('form');
    const formBox = await form.boundingBox();
    const authBoxBounds = await authBox.boundingBox();
    expect(formBox).not.toBeNull();
    expect(authBoxBounds).not.toBeNull();
    const formCenter = (formBox?.x ?? 0) + (formBox?.width ?? 0) / 2;
    const authBoxCenter = (authBoxBounds?.x ?? 0) + (authBoxBounds?.width ?? 0) / 2;
    expect(Math.abs(formCenter - authBoxCenter)).toBeLessThanOrEqual(1);
    const controls = form.locator('input, .btn-submit');
    const widths = await controls.evaluateAll(elements => elements.map(element => Math.round(element.getBoundingClientRect().width)));
    expect(new Set(widths).size).toBe(1);
    await expect(page.getByRole('navigation').getByRole('link', { name: 'Criar conta' })).not.toHaveClass(/button/);
});

test('criar conta abre e conclui o cadastro público', async ({ page }, testInfo) => {
    await page.goto('/login.php');
    await page.getByRole('navigation').getByRole('link', { name: 'Criar conta' }).click();
    await expect(page).toHaveURL(/register\.php/);
    const username = `cadastro_e2e_${testInfo.project.name}`;
    await page.getByPlaceholder('Nome completo').fill('Cadastro E2E');
    await page.getByPlaceholder('Usuário').fill(username);
    await page.getByPlaceholder('E-mail').fill(`${username}@example.test`);
    await page.getByPlaceholder('Senha', { exact: true }).fill('Cadastro123!');
    await page.getByPlaceholder('Confirme a senha').fill('Cadastro123!');
    await page.getByRole('button', { name: 'Finalizar cadastro' }).click();
    await expect(page.locator('.toast')).toContainText('Conta criada');
});

test('elementos visíveis mantêm contraste confortável nos temas', async ({ page }) => {
    for (const theme of ['light', 'dark', 'custom']) {
        await page.goto('/login.php');
        await page.evaluate(themeName => {
            if (themeName === 'dark') localStorage.setItem('theme', 'dark');
            else localStorage.removeItem('theme');
            if (themeName === 'custom') {
                document.body.classList.add('custom-theme');
                document.body.style.setProperty('--custom-primary', '#123456');
                document.body.style.setProperty('--custom-primary-ink', '#FFFFFF');
                document.body.style.setProperty('--custom-secondary', '#ABCDEF');
                document.body.style.setProperty('--custom-surface-light', '#E1E7EE');
                document.body.style.setProperty('--custom-surface-dark', '#030A11');
                document.body.style.setProperty('--custom-secondary-readable-light', '#123456');
                document.body.style.setProperty('--custom-secondary-readable-dark', '#ABCDEF');
                document.body.style.setProperty('--custom-secondary-ink-light', '#FFFFFF');
                document.body.style.setProperty('--custom-secondary-ink-dark', '#FFFFFF');
                document.body.style.setProperty('--custom-header-light', '#DDE3EA');
                document.body.style.setProperty('--custom-header-dark', '#07101A');
                document.body.style.setProperty('--custom-header-text-light', '#123456');
                document.body.style.setProperty('--custom-header-text-dark', '#ABCDEF');
            }
        }, theme);
        await page.reload();
        if (theme === 'custom') {
            await page.evaluate(() => {
                document.body.classList.add('custom-theme');
                document.body.style.setProperty('--custom-primary', '#123456');
                document.body.style.setProperty('--custom-primary-ink', '#FFFFFF');
                document.body.style.setProperty('--custom-secondary', '#ABCDEF');
                document.body.style.setProperty('--custom-surface-light', '#E1E7EE');
                document.body.style.setProperty('--custom-surface-dark', '#030A11');
                document.body.style.setProperty('--custom-secondary-readable-light', '#123456');
                document.body.style.setProperty('--custom-secondary-readable-dark', '#ABCDEF');
                document.body.style.setProperty('--custom-secondary-ink-light', '#FFFFFF');
                document.body.style.setProperty('--custom-secondary-ink-dark', '#FFFFFF');
                document.body.style.setProperty('--custom-header-light', '#DDE3EA');
                document.body.style.setProperty('--custom-header-dark', '#07101A');
                document.body.style.setProperty('--custom-header-text-light', '#123456');
                document.body.style.setProperty('--custom-header-text-dark', '#ABCDEF');
            });
        }
        await page.mouse.move(-10, -10);
        await page.waitForTimeout(250);
        const violations = await page.locator('body *').evaluateAll(elements => {
            const luminance = (color) => {
                const match = color.match(/\d+(?:\.\d+)?/g);
                if (!match || match.length < 3) return null;
                return match.slice(0, 3).map(Number).map(channel => {
                    const value = channel / 255;
                    return value <= 0.03928 ? value / 12.92 : ((value + 0.055) / 1.055) ** 2.4;
                }).reduce((sum, value, index) => sum + value * [0.2126, 0.7152, 0.0722][index], 0);
            };
            const ratio = (foreground, background) => {
                const fg = luminance(foreground);
                const bg = luminance(background);
                return fg === null || bg === null ? null : (Math.max(fg, bg) + 0.05) / (Math.min(fg, bg) + 0.05);
            };
            const effectiveBackground = (element) => {
                let current = element;
                while (current) {
                    const background = getComputedStyle(current).backgroundColor;
                    if (background !== 'rgba(0, 0, 0, 0)') return background;
                    current = current.parentElement;
                }
                return 'rgb(255, 253, 249)';
            };
            return elements.filter(element => {
                const style = getComputedStyle(element);
                const hasText = element.textContent.trim().length > 0;
                const isVisible = style.display !== 'none' && style.visibility !== 'hidden' && Number(style.opacity) > 0;
                if (!hasText || !isVisible) return false;
                const background = effectiveBackground(element);
                const contrast = ratio(style.color, background);
                return contrast !== null && contrast < 4.5;
            }).map(element => {
                const style = getComputedStyle(element);
                return `${element.tagName}.${element.className} (${style.color} on ${effectiveBackground(element)})`;
            });
        });
        expect(violations, `Contraste insuficiente no tema ${theme}: ${violations.join(', ')}`).toEqual([]);
    }
});

test('leitor faz login, comenta e reage', async ({ page, users, login }, testInfo) => {
    await login(users.leitor);
    await page.goto('/');
    const post = page.locator('article.post').filter({ hasText: 'História de teste' });
    const likeButton = post.locator('button.reaction[data-type="like"]');
    const initialLikes = await likeButton.textContent();
    await post.locator('button.reaction[data-type="like"]').click();
    await expect(likeButton).not.toHaveText(initialLikes);
    await post.locator('textarea[name="comment"]').fill(`Comentário criado no teste ${testInfo.project.name}.`);
    await post.getByRole('button', { name: 'Comentar' }).click();
    await expect(page.getByText(`Comentário criado no teste ${testInfo.project.name}`).first()).toBeVisible();
});

test('usuário logado acessa e salva seu tema pelo perfil', async ({ page, users, login }) => {
    await login(users.leitor);
    const profileLink = page.getByRole('link', { name: 'Meu perfil' });
    await expect(profileLink).toBeVisible();
    await expect(profileLink).not.toHaveClass(/button/);
    await profileLink.click();
    await expect(page).toHaveURL(/profile\.php/);
    await page.locator('input[name="tema_cor_principal"]').fill('#123456');
    await page.locator('input[name="tema_cor_secundaria"]').fill('#ABCDEF');
    await page.getByRole('button', { name: 'Salvar alterações' }).click();
    await expect(page.locator('body')).toHaveClass(/custom-theme/);
    await expect(page.locator('body')).toHaveAttribute('style', /--custom-primary: #123456/);
    const toast = page.locator('.toast');
    await expect(toast).toBeVisible();
    await toast.hover();
    await page.waitForTimeout(5200);
    await expect(toast).not.toHaveClass(/is-hidden/);
    await page.mouse.move(0, 0);
    await expect(toast).toHaveClass(/is-hidden/, { timeout: 6000 });
    await page.getByRole('link', { name: 'Sair' }).click();
    await expect(page).toHaveURL(/login\.php/);
    await expect(page.locator('body')).not.toHaveClass(/custom-theme/);
});

test('usuário restaura o tema padrão pela conta', async ({ page, users, login }) => {
    await login(users.leitor);
    await page.goto('/profile.php');
    await page.locator('input[name="tema_cor_principal"]').fill('#00CC55');
    await page.locator('input[name="tema_cor_secundaria"]').fill('#001122');
    await page.getByRole('button', { name: 'Salvar alterações' }).click();
    await expect(page.locator('body')).toHaveClass(/custom-theme/);
    await page.getByRole('button', { name: 'Voltar ao tema padrão' }).click();
    await expect(page.locator('body')).not.toHaveClass(/custom-theme/);
    await expect(page.locator('.toast')).toContainText('Tema padrão restaurado');
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
