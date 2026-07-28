const { NodeSSH } = require('node-ssh');
const ssh = new NodeSSH();
async function main() {
    await ssh.connect({ host: '163.245.222.149', username: 'root', password: 'Y2!6mYky' });
    const command = `(crontab -l 2>/dev/null; echo "* * * * * cd /var/www/html/psicoguia && php artisan schedule:run >> /dev/null 2>&1") | crontab -`;
    await ssh.execCommand(command);
    console.log('Crontab updated');
    ssh.dispose();
}
main();
