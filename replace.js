const fs = require('fs');
function walk(dir) {
    let results = [];
    const list = fs.readdirSync(dir);
    list.forEach(function(file) {
        file = dir + '/' + file;
        const stat = fs.statSync(file);
        if (stat && stat.isDirectory()) { 
            results = results.concat(walk(file));
        } else { 
            if(file.endsWith('.vue')) results.push(file);
        }
    });
    return results;
}
const files = walk('d:/KruChamrong/TimeTable/tsms/resources/js/Pages');
const regex = /([\u1780-\u17FF])\s?\([A-Za-z][A-Za-z\s/-]+\)/g;
let replaced = 0;
files.forEach(file => {
    let content = fs.readFileSync(file, 'utf8');
    let newContent = content.replace(regex, '$1');
    if (content !== newContent) {
        fs.writeFileSync(file, newContent, 'utf8');
        console.log('Updated ' + file);
        replaced++;
    }
});
console.log('Total files updated: ' + replaced);
