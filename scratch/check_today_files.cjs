const fs = require('fs');
const path = require('path');

function getFiles(dir, files = []) {
  const items = fs.readdirSync(dir, { withFileTypes: true });
  for (const item of items) {
    if (item.name === '.git' || item.name === 'node_modules' || item.name === 'uploads' || item.name === 'scratch') continue;
    const fullPath = path.join(dir, item.name);
    if (item.isDirectory()) {
      getFiles(fullPath, files);
    } else {
      const stats = fs.statSync(fullPath);
      // check if modified today (after 2026-10-30 00:00:00)
      const today = new Date('2026-10-30T00:00:00');
      if (stats.mtime >= today) {
        files.push({ file: fullPath.replace(/\\/g, '/'), mtime: stats.mtime });
      }
    }
  }
  return files;
}

const crmFiles = getFiles('crm.hcinterior.in');
console.log('CRM files modified today:');
crmFiles.forEach(f => console.log(f.file + ' --- ' + f.mtime.toLocaleTimeString()));

const srcFiles = getFiles('src');
console.log('\nSRC files modified today:');
srcFiles.forEach(f => console.log(f.file + ' --- ' + f.mtime.toLocaleTimeString()));

