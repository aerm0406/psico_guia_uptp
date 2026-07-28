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
    console.log('Connected. Uploading build files...');

    const remotePath = '/var/www/html/psicoguia';
    
    // Upload manifest
    await ssh.putFile('public/build/manifest.json', `${remotePath}/public/build/manifest.json`);
    console.log('Uploaded manifest.json');
    
    // Upload assets directory
    await ssh.putDirectory('public/build/assets', `${remotePath}/public/build/assets`);
    console.log('Uploaded assets directory');

    console.log('Upload successful!');
  } catch (error) {
    console.error('Error:', error);
  } finally {
    ssh.dispose();
  }
}

main();
