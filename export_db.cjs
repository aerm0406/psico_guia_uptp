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
    console.log('Connected. Exporting database...');

    const dumpCmd = 'mysqldump -u psicoguia -pPsicoPass123! psicoguia > /root/psicoguia_dump.sql';
    const result = await ssh.execCommand(dumpCmd);
    if (result.stderr) {
        console.log('mysqldump stderr (could be warnings):', result.stderr);
    }

    console.log('Downloading dump to local folder...');
    const localFile = 'c:\\xampp\\htdocs\\psico_guia_uptp\\psicoguia_export.sql';
    await ssh.getFile(localFile, '/root/psicoguia_dump.sql');
    
    console.log('Download complete. Deleting remote dump...');
    await ssh.execCommand('rm /root/psicoguia_dump.sql');
    
    console.log('Database exported successfully to psicoguia_export.sql');
  } catch (error) {
    console.error('Error:', error);
  } finally {
    ssh.dispose();
  }
}

main();
