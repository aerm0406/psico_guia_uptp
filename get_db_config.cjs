const { NodeSSH } = require('node-ssh');
const ssh = new NodeSSH();
const fs = require('fs');

async function main() {
  try {
    console.log('Connecting...');
    await ssh.connect({
      host: '163.245.222.149',
      username: 'root',
      password: 'Y2!6mYky'
    });
    console.log('Connected. Getting DB credentials...');

    const result = await ssh.execCommand('cat /var/www/html/psicoguia/.env | grep DB_');
    console.log('DB Config:\n', result.stdout);

    fs.writeFileSync('db_config.txt', result.stdout);
    
  } catch (error) {
    console.error('Error:', error);
  } finally {
    ssh.dispose();
  }
}

main();
