/**
 * Pipeline check: asserts jQuery is absent from the built bundle.
 * Run after `npm run build` to verify no jQuery ships.
 *
 * Usage: node tools/lint-no-jquery.mjs
 */

import { readFileSync, readdirSync, existsSync } from 'fs';
import { join } from 'path';

const buildDir = join(process.cwd(), 'public', 'build', 'assets');

if (!existsSync(buildDir)) {
    console.log('Build directory not found. Run `npm run build` first.');
    console.log('SKIP: No build output to check.');
    process.exit(0);
}

const jsFiles = readdirSync(buildDir).filter(f => f.endsWith('.js'));
let found = false;

for (const file of jsFiles) {
    const content = readFileSync(join(buildDir, file), 'utf-8');

    if (content.includes('jQuery') || content.includes('jquery')) {
        console.error(`ERROR: jQuery detected in ${file}`);
        found = true;
    }
}

if (found) {
    console.error('\nFAILED: jQuery must not be present in the shipped bundle.');
    process.exit(1);
} else {
    console.log(`Checked ${jsFiles.length} JS file(s). No jQuery found. ✓`);
    process.exit(0);
}
