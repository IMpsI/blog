const { defineConfig, devices } = require('@playwright/test');

module.exports = defineConfig({
    testDir: './tests/e2e',
    globalSetup: './tests/e2e/global-setup.js',
    timeout: 30_000,
    expect: { timeout: 5_000 },
    fullyParallel: false,
    workers: 1,
    reporter: [['list']],
    use: {
        baseURL: 'http://127.0.0.1:8088',
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
    },
    projects: [
        { name: 'desktop', use: { ...devices['Desktop Chrome'] } },
        {
            name: 'mobile',
            use: {
                ...devices['iPhone 13'],
                browserName: 'chromium',
            },
        },
    ],
    webServer: {
        command: 'cmd /c "set BLOG_DB_NAME=meublog_test&& C:\\xampp\\php\\php.exe -S 127.0.0.1:8088 -t ."',
        url: 'http://127.0.0.1:8088',
        reuseExistingServer: false,
        timeout: 15_000,
    },
});
