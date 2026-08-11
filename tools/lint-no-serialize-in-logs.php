#!/usr/bin/env php
<?php

/**
 * Static Lint Rule: No serialize() in log calls
 *
 * Scans PHP source files for serialize() used inside logger()/Log:: calls.
 * This is a required build check that prevents PII from being serialized
 * into log output.
 *
 * Usage: php tools/lint-no-serialize-in-logs.php [path]
 *
 * Exit codes:
 *   0 = no violations found
 *   1 = one or more violations found
 */

$searchPath = $argv[1] ?? dirname(__DIR__) . '/app';

if (!is_dir($searchPath) && !is_file($searchPath)) {
    fwrite(STDERR, "ERROR: Path not found: {$searchPath}\n");
    exit(1);
}

$patterns = [
    '/logger\(\)\s*->\s*\w+\s*\([^)]*serialize\s*\(/m',
    '/Log\s*::\s*\w+\s*\([^)]*serialize\s*\(/m',
    '/log\(\)\s*->\s*\w+\s*\([^)]*serialize\s*\(/m',
    "/logger\\(\\)\\s*->\\s*\\w+\\s*\\([^;]*serialize\\s*\\(/ms",
    "/Log\\s*::\\s*\\w+\\s*\\([^;]*serialize\\s*\\(/ms",
];

$violations = [];

$iterator = is_file($searchPath)
    ? [new SplFileInfo($searchPath)]
    : new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($searchPath, RecursiveDirectoryIterator::SKIP_DOTS)
    );

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }

    $filepath = $file->getRealPath();
    $contents = file_get_contents($filepath);

    if ($contents === false) {
        continue;
    }

    $lines = explode("\n", $contents);

    foreach ($lines as $lineNum => $line) {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $line)) {
                $violations[] = [
                    'file' => $filepath,
                    'line' => $lineNum + 1,
                    'code' => trim($line),
                ];
                break;
            }
        }
    }
}

echo "=== Lint: No serialize() in log calls ===\n";
echo "Scanned: {$searchPath}\n\n";

if (count($violations) === 0) {
    echo "PASS: No serialize() calls found inside log statements.\n";
    exit(0);
}

echo "FAIL: " . count($violations) . " violation(s) found:\n\n";

foreach ($violations as $v) {
    $relPath = str_replace(dirname(__DIR__) . '/', '', $v['file']);
    echo "  {$relPath}:{$v['line']}\n";
    echo "    {$v['code']}\n\n";
}

echo "Rule: serialize() must never appear inside logger()/Log:: calls.\n";
echo "Fix: Use structured arrays with specific keys instead of serialize().\n";
exit(1);
