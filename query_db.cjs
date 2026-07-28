const { NodeSSH } = require('node-ssh');
const ssh = new NodeSSH();
async function main() {
    await ssh.connect({ host: '163.245.222.149', username: 'root', password: 'Y2!6mYky' });
    const res = await ssh.execCommand('cd /var/www/html/psicoguia && php artisan tinker --execute="echo \\App\\Models\\Cita::where(\'paciente\', \'LIKE\', \'%Jaccielys%\')->pluck(\'motivo\')->toJson();"');
    console.log("Output:");
    console.log(res.stdout);
    if(res.stderr) console.log("Error:", res.stderr);
    ssh.dispose();
}
main();
