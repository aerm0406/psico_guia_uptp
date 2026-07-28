const fs = require('fs');

const files = [
    'routes/web.php'
];

let scriptContent = fs.readFileSync('upload_fix.cjs', 'utf-8');
const filesRegex = /const files = \[([\s\S]*?)\];/;
scriptContent = scriptContent.replace(filesRegex, `const files = [\n    '${files.join("',\n    '")}'\n];`);

fs.writeFileSync('upload_fix.cjs', scriptContent);
console.log("Upload script updated.");
