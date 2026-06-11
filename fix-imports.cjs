const fs = require('fs');
const path = require('path');

function fixFile(filePath) {
    let content = fs.readFileSync(filePath, 'utf8');
    const original = content;
    content = content.replace(/@\/components\//g, '@/Components/');
    if (content !== original) {
        fs.writeFileSync(filePath, content);
        console.log('Fixed:', filePath);
    }
}

function walkDir(dir) {
    const files = fs.readdirSync(dir);
    for (const file of files) {
        const filePath = path.join(dir, file);
        const stat = fs.statSync(filePath);
        if (stat.isDirectory() && !file.includes('node_modules')) {
            walkDir(filePath);
        } else if (file.endsWith('.tsx') || file.endsWith('.ts')) {
            fixFile(filePath);
        }
    }
}

walkDir('./resources/js');
console.log('Done!');