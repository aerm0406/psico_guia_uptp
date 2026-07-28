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

    console.log('Uploading historias/index.blade.php...');
    await ssh.putFile('resources/views/historias/index.blade.php', `${remotePath}/resources/views/historias/index.blade.php`);

    console.log('Uploading plantillas_globales/index.blade.php...');
    await ssh.putFile('resources/views/plantillas_globales/index.blade.php', `${remotePath}/resources/views/plantillas_globales/index.blade.php`);

    console.log('Uploading manifest.json...');
    await ssh.putFile('public/build/manifest.json', `${remotePath}/public/build/manifest.json`);

    console.log('Uploading assets directory...');
    await ssh.putDirectory('public/build/assets', `${remotePath}/public/build/assets`);

    console.log('All responsive fix files uploaded successfully to VPS!');
  } catch (error) {
    console.error('Error uploading to VPS:', error);
  } finally {
    ssh.dispose();
  }
}

main();
