const { NodeSSH } = require('node-ssh');
const ssh = new NodeSSH();
async function main() {
    await ssh.connect({ host: '163.245.222.149', username: 'root', password: 'Y2!6mYky' });
    const res = await ssh.execCommand('grep -A 10 -B 2 "isManual" /var/www/html/psicoguia/app/Models/Cita.php');
    console.log(res.stdout);
    ssh.dispose();
}
main();
