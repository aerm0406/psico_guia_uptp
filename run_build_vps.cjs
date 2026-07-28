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
    console.log('Connected. Running npm run build...');
    
    const result = await ssh.execCommand('npm run build', { cwd: '/var/www/html/psicoguia' });
    console.log('STDOUT:', result.stdout);
    console.log('STDERR:', result.stderr);
    
    console.log('Build successful!');
  } catch (error) {
    console.error('Error:', error);
  } finally {
    ssh.dispose();
  }
}

main();
