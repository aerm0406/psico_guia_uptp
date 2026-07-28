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
    console.log('Connected. Uploading files...');

    const remotePath = '/var/www/html/psicoguia';
    
    console.log('Clearing view cache on the server...');
    await ssh.execCommand('php artisan view:clear', { cwd: remotePath });
    
    console.log('Done!');
  } catch (error) {
    console.error('Error:', error);
  } finally {
    ssh.dispose();
  }
}

main();
