import fs from 'node:fs';
import path from 'node:path';
import { gzipSync } from 'node:zlib';

const buildRoot = path.resolve('public/build/assets');
if (!fs.existsSync(buildRoot)) {
    console.error('Build output missing: run npm run build before checking performance budgets.');
    process.exit(1);
}

const assets = [];
function walk(directory) {
    for (const entry of fs.readdirSync(directory, { withFileTypes: true })) {
        const file = path.join(directory, entry.name);
        if (entry.isDirectory()) walk(file);
        else if (/\.(?:js|css)$/i.test(entry.name)) {
            const bytes = fs.readFileSync(file);
            assets.push({ file: path.relative('public/build', file), type: path.extname(file).slice(1), raw: bytes.length, gzip: gzipSync(bytes).length });
        }
    }
}
walk(buildRoot);

const totals = assets.reduce((sum, asset) => {
    sum[asset.type].raw += asset.raw;
    sum[asset.type].gzip += asset.gzip;
    return sum;
}, { js: { raw: 0, gzip: 0 }, css: { raw: 0, gzip: 0 } });

const budgets = {
    // Initial CI baseline: 23.6 KiB JS and 67.2 KiB CSS gzip on the 2026-11-21 production build.
    // Allow headroom while still detecting substantial unreviewed growth.
    js: Number(process.env.ZAZU_JS_GZIP_BUDGET || 64 * 1024),
    css: Number(process.env.ZAZU_CSS_GZIP_BUDGET || 100 * 1024),
};
let failed = false;
for (const type of ['js', 'css']) {
    const actual = totals[type].gzip;
    const budget = budgets[type];
    console.log(`${type.toUpperCase()}: ${(actual / 1024).toFixed(1)} KiB gzip / ${(budget / 1024).toFixed(1)} KiB budget (${(totals[type].raw / 1024).toFixed(1)} KiB raw)`);
    if (actual > budget) {
        console.error(`FAIL ${type.toUpperCase()} compressed asset budget exceeded.`);
        failed = true;
    }
}
for (const asset of assets.sort((a, b) => b.gzip - a.gzip).slice(0, 8)) {
    console.log(`  ${asset.file}: ${(asset.gzip / 1024).toFixed(1)} KiB gzip`);
}
if (failed) process.exit(1);
console.log('Frontend compressed asset budgets passed. These are transfer-size guardrails, not a substitute for real-device runtime profiling.');
