const { NodeSSH } = require('node-ssh');
const ssh = new NodeSSH();

async function main() {
  try {
    console.log('Connecting...');
    await ssh.connect({
      host: '163.245.222.149',
      username: 'root',
      password: 'Y2!6mYky'
    });
    console.log('Connected. Running migration...');

    const remotePath = '/var/www/html/psicoguia';
    
    const result = await ssh.execCommand('php artisan migrate --force', { cwd: remotePath });
    console.log('STDOUT:', result.stdout);
    console.log('STDERR:', result.stderr);
    
    console.log('Migration command executed!');
  } catch (error) {
    console.error('Error:', error);
  } finally {
    ssh.dispose();
  }
}

main();
