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
        'app/Models/User.php',
        'app/Http/Controllers/Auth/PasswordResetLinkController.php',
        'app/Http/Controllers/Auth/SecurityQuestionResetController.php',
        'app/Http/Controllers/Auth/NewPasswordController.php',
        'app/Http/Controllers/Auth/PasswordController.php',
        'app/Http/Controllers/Auth/ConfirmablePasswordController.php',
        'app/Http/Requests/Auth/LoginRequest.php',
        'routes/auth.php',
        'resources/views/auth/login.blade.php',
        'resources/views/auth/register.blade.php',
        'resources/views/auth/forgot-password.blade.php',
        'resources/views/auth/forgot-password-cedula.blade.php',
        'resources/views/auth/security-questions.blade.php',
        'resources/views/auth/reset-password.blade.php',
        'resources/views/auth/verify-email.blade.php',
        'resources/views/auth/confirm-password.blade.php',
        'resources/views/profile/partials/update-password-form.blade.php',
        'resources/views/profile/partials/update-profile-information-form.blade.php',
        'resources/views/profile/partials/delete-user-form.blade.php',
        'resources/views/profile/complete.blade.php',
        'resources/views/layouts/app.blade.php',
        'resources/views/welcome.blade.php',
        'resources/views/layouts/guest.blade.php',
        'resources/views/components/theme-switcher.blade.php'
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
