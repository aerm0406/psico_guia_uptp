const { NodeSSH } = require('node-ssh');
const ssh = new NodeSSH();

async function main() {
  try {
    console.log('Connecting to VPS...');
    await ssh.connect({
      host: '163.245.222.149',
      username: 'root',
      password: 'Y2!6mYky'
    });
    console.log('Connected.');

    const remotePath = '/var/www/html/psicoguia';

    console.log('Uploading navigation.blade.php...');
    await ssh.putFile('resources/views/layouts/navigation.blade.php', `${remotePath}/resources/views/layouts/navigation.blade.php`);

    console.log('Uploading app.css...');
    await ssh.putFile('resources/css/app.css', `${remotePath}/resources/css/app.css`);

    console.log('Uploading manifest.json...');
    await ssh.putFile('public/build/manifest.json', `${remotePath}/public/build/manifest.json`);

    console.log('Uploading assets directory...');
    await ssh.putDirectory('public/build/assets', `${remotePath}/public/build/assets`);

    console.log('All files uploaded successfully to VPS!');
  } catch (error) {
    console.error('Error uploading to VPS:', error);
  } finally {
    ssh.dispose();
  }
}

main();
