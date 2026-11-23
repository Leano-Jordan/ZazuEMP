import { spawnSync } from 'node:child_process';

const result = spawnSync('npm', ['audit', '--audit-level=high'], {
    encoding: 'utf8',
    maxBuffer: 10 * 1024 * 1024,
});

if (result.error) {
    console.error('npm audit could not run:', result.error.message);
    process.exit(1);
}

if (result.stdout) process.stdout.write(result.stdout);
if (result.stderr) process.stderr.write(result.stderr);

if (result.status !== 0) {
    process.exit(result.status ?? 1);
}

console.log('npm audit passed: no high/critical vulnerabilities.');
