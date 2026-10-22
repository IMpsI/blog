const { execFileSync } = require('node:child_process');

module.exports = async () => {
    execFileSync('C:\\xampp\\php\\php.exe', ['tests\\e2e\\setup.php'], {
        cwd: process.cwd(),
        stdio: 'inherit',
    });
};
