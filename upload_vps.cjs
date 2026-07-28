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
        'app/Http/Controllers/ChatController.php',
        'app/Events/MessageSent.php',
        'app/Notifications/NewMessageNotification.php',
        'resources/views/chat/index.blade.php',
        'resources/views/components/chat-window.blade.php',
        'routes/channels.php',
        'resources/views/agenda/index.blade.php',
        'routes/web.php',
        'resources/views/dashboard_paciente.blade.php',
        'resources/views/welcome.blade.php'

    ];

    for (const file of files) {
        const localFile = file;
        const remoteFile = `${remotePath}/${file}`;
        console.log(`Uploading ${localFile} to ${remoteFile}...`);
        await ssh.putFile(localFile, remoteFile);
    }
    
    console.log('Restarting Reverb and Queue...');
    await ssh.execCommand('php artisan reverb:restart', { cwd: remotePath });
    await ssh.execCommand('php artisan queue:restart', { cwd: remotePath });
    await ssh.execCommand('php artisan view:clear', { cwd: remotePath });
    
    console.log('Upload successful!');
  } catch (error) {
    console.error('Error:', error);
  } finally {
    ssh.dispose();
  }
}

main();
