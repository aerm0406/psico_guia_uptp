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

    console.log('Uploading auth/login.blade.php...');
    await ssh.putFile('resources/views/auth/login.blade.php', `${remotePath}/resources/views/auth/login.blade.php`);

    console.log('Uploading auth/register.blade.php...');
    await ssh.putFile('resources/views/auth/register.blade.php', `${remotePath}/resources/views/auth/register.blade.php`);

    console.log('Uploading citas/edit_note.blade.php...');
    await ssh.putFile('resources/views/citas/edit_note.blade.php', `${remotePath}/resources/views/citas/edit_note.blade.php`);

    console.log('Uploading manifest.json...');
    await ssh.putFile('public/build/manifest.json', `${remotePath}/public/build/manifest.json`);

    console.log('Uploading assets directory...');
    await ssh.putDirectory('public/build/assets', `${remotePath}/public/build/assets`);

    console.log('Clearing Laravel view cache...');
    await ssh.execCommand('php artisan view:clear', { cwd: remotePath });

    console.log('All card proportion fix files uploaded successfully to VPS!');
  } catch (error) {
    console.error('Error uploading to VPS:', error);
  } finally {
    ssh.dispose();
  }
}

main();
