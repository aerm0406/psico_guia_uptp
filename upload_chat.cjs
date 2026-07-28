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
    const files = [
        'resources/views/chat/index.blade.php'
    ];

    for (const file of files) {
        const localFile = file;
        const remoteFile = `${remotePath}/${file}`;
        console.log(`Uploading ${localFile} to ${remoteFile}...`);
        await ssh.putFile(localFile, remoteFile);
    }
    
    console.log('Upload successful!');
  } catch (error) {
    console.error('Error:', error);
  } finally {
    ssh.dispose();
  }
}

main();
