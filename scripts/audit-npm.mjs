import { spawnSync } from 'node:child_process';

const acceptedAdvisory = 'GHSA-fc5q-39xc-vjpf';
const result = spawnSync('npm', ['audit', '--json'], {
    encoding: 'utf8',
    maxBuffer: 10 * 1024 * 1024,
});

if (result.error) {
    console.error('npm audit could not run:', result.error.message);
    process.exit(1);
}

let report;
try {
    report = JSON.parse(result.stdout);
} catch {
    console.error('npm audit did not return valid JSON.');
    if (result.stderr) console.error(result.stderr);
    process.exit(1);
}

if (report.error) {
    console.error('npm audit failed:', report.error.summary || report.error.message);
    process.exit(1);
}

const vulnerabilities = Object.entries(report.vulnerabilities || {});
const accepted = new Set();
let changed = true;

// Accept only the named, currently unpatched advisory and packages whose
// complete advisory chain leads exclusively to that exact finding.
while (changed) {
    changed = false;

    for (const [name, vulnerability] of vulnerabilities) {
        if (accepted.has(name)) continue;

        const via = vulnerability.via || [];
        if (via.length === 0) continue;

        const onlyAcceptedAdvisory = via.every((item) => {
            if (typeof item === 'string') return accepted.has(item);

            return (item.source === acceptedAdvisory || item.url?.endsWith(acceptedAdvisory))
                && item.name === 'picomatch';
        });

        if (onlyAcceptedAdvisory) {
            accepted.add(name);
            changed = true;
        }
    }
}

const blocked = vulnerabilities.filter(([name, vulnerability]) =>
    ['high', 'critical'].includes(vulnerability.severity) && !accepted.has(name)
);

for (const [name, vulnerability] of vulnerabilities) {
    if (accepted.has(name)) {
        console.warn(
            `Accepted temporary exception: ${name} (${vulnerability.severity}) via ${acceptedAdvisory}; no fix is available.`,
        );
    }
}

if (blocked.length > 0) {
    console.error('Unaccepted high/critical npm vulnerabilities:');
    for (const [name, vulnerability] of blocked) {
        console.error(`- ${name}: ${vulnerability.severity}`);
    }
    process.exit(1);
}

if (accepted.size > 0) {
    console.warn(
        `npm audit completed with a narrowly scoped exception for ${acceptedAdvisory}. All other high/critical findings remain blocking.`,
    );
} else {
    console.log('npm audit passed: no high/critical vulnerabilities.');
}
