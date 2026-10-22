const { execFileSync } = require('node:child_process');

module.exports = async () => {
    try {
        execFileSync('C:\\xampp\\php\\php.exe', ['tests\\e2e\\setup.php'], {
            cwd: process.cwd(),
            stdio: 'inherit',
        });
    } catch (error) {
        throw new Error(
            'Não foi possível preparar o banco do E2E. Inicie o MySQL no XAMPP (ou o serviço MySQL80) e execute npm run test:e2e novamente.',
            { cause: error }
        );
    }
};
