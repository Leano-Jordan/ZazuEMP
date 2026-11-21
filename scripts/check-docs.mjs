import fs from 'node:fs';
import path from 'node:path';

const root = process.cwd();
const ignored = new Set(['.git', 'node_modules', 'vendor', 'public/build', 'storage']);
const markdownFiles = [];

function walk(directory) {
    for (const entry of fs.readdirSync(directory, { withFileTypes: true })) {
        if (ignored.has(entry.name)) continue;
        const absolute = path.join(directory, entry.name);
        if (entry.isDirectory()) walk(absolute);
        else if (entry.isFile() && entry.name.toLowerCase().endsWith('.md')) markdownFiles.push(absolute);
    }
}

walk(root);
let failures = 0;
let checkedLinks = 0;

for (const file of markdownFiles) {
    const source = fs.readFileSync(file, 'utf8');
    const relative = path.relative(root, file);
    // Archived engineering records preserve historical references; they are not maintained operational documentation.
    if (relative.split(path.sep).join('/').startsWith('docs/archive/')) continue;
    const linkPattern = /!?(?:\[[^\]]*\])\(([^)]+)\)/g;
    for (const match of source.matchAll(linkPattern)) {
        const raw = match[1].trim().replace(/^<|>$/g, '');
        if (!raw || /^(?:https?:|mailto:|tel:|#|data:)/i.test(raw)) continue;
        const target = decodeURIComponent(raw.split('#')[0].split('?')[0]);
        if (!target) continue;
        checkedLinks++;
        const resolved = path.resolve(path.dirname(file), target);
        if (!resolved.startsWith(root + path.sep) && resolved !== root) {
            console.error(`FAIL ${relative}: link escapes repository: ${raw}`);
            failures++;
            continue;
        }
        if (!fs.existsSync(resolved)) {
            console.error(`FAIL ${relative}: missing local link: ${raw}`);
            failures++;
        }
    }
}

const required = [
    ['README.md', ['Windows installation candidate', 'Security and privacy', 'Documentation rule']],
    ['docs/V1_PACKAGE_INSTALLATION.md', ['Host requirements', 'Fresh installation', 'Data safety and updates']],
    ['docs/LOCAL_PC_HOST_DEPLOYMENT.md', ['HTTPS', 'backup']],
    ['docs/RELEASE_BACKUP_RESTORE_RUNBOOK.md', ['backup', 'restore']],
    ['docs/QUALITY_GATES.md', ['Frontend performance baseline', 'Accessibility', 'Database compatibility', 'Release integrity']],
    ['SECURITY.md', ['report', 'security']],
];
for (const [file, headings] of required) {
    const absolute = path.join(root, file);
    if (!fs.existsSync(absolute)) {
        console.error(`FAIL required operational document missing: ${file}`);
        failures++;
        continue;
    }
    const content = fs.readFileSync(absolute, 'utf8').toLowerCase();
    for (const phrase of headings) {
        if (!content.includes(phrase.toLowerCase())) {
            console.error(`FAIL ${file}: expected operational topic missing: ${phrase}`);
            failures++;
        }
    }
}

if (failures) {
    console.error(`Documentation validation failed: ${failures} issue(s), ${checkedLinks} local links checked across ${markdownFiles.length} Markdown files.`);
    process.exit(1);
}
console.log(`Documentation validation passed: ${checkedLinks} local links checked across ${markdownFiles.length} Markdown files; required installation, security and recovery topics present.`);
