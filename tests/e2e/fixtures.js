const { test: base, expect } = require('@playwright/test');

const users = {
    leitor: { username: 'teste_leitor', password: 'Leitor123!' },
    autor: { username: 'teste_autor', password: 'Autor123!' },
    outro: { username: 'teste_outro', password: 'Outro123!' },
    admin: { username: 'teste_admin', password: 'Admin123!' },
    pendente: { username: 'teste_pendente', password: 'Leitor123!' },
};

async function login(page, user) {
    await page.goto('/login.php');
    await page.getByPlaceholder('Usuário').fill(user.username);
    await page.getByPlaceholder('Senha').fill(user.password);
    await page.getByRole('button', { name: 'Entrar' }).click();
}

const test = base.extend({
    users: async ({}, use) => use(users),
    login: async ({ page }, use) => use((user) => login(page, user)),
});

module.exports = { test, expect, users, login };
