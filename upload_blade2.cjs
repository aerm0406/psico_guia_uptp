const Client = require('ssh2').Client;
const fs = require('fs');

const conn = new Client();
const filesToUpload = [
    { local: 'resources/views/agenda/index.blade.php', remote: '/var/www/html/psicoguia/resources/views/agenda/index.blade.php' }
];

conn.on('ready', () => {
    console.log('Connected. Uploading files...');
    conn.sftp((err, sftp) => {
        if (err) throw err;
        let pending = filesToUpload.length;
        filesToUpload.forEach(file => {
            console.log(`Uploading ${file.local} to ${file.remote}...`);
            sftp.fastPut(file.local, file.remote, (err) => {
                if (err) throw err;
                pending--;
                if (pending === 0) {
                    console.log('Upload successful!');
                    conn.end();
                }
            });
        });
    });
}).connect({
    host: '163.245.222.149',
    port: 22,
    username: 'root',
    password: 'Y2!6mYky'
});
