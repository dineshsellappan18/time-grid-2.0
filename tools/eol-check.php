#!/usr/bin/env php
<?php

/**
 * End-of-Life Stack Assertion
 *
 * This script is a required pipeline check that fails the build if:
 * 1. The PHP constraint does not include ^8.3|^8.4
 * 2. The laravel/framework constraint drops below 13.x
 * 3. Any resolved package declares constraints incompatible with PHP 8.3/8.4
 *
 * Usage: php tools/eol-check.php [--composer-json=path]
 *
 * Exit codes:
 *   0 = all assertions pass
 *   1 = one or more assertions failed
 */

$composerPath = $argv[1] ?? dirname(__DIR__) . '/composer.json';

if (!file_exists($composerPath)) {
    fwrite(STDERR, "ERROR: composer.json not found at: {$composerPath}\n");
    exit(1);
}

$composer = json_decode(file_get_contents($composerPath), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    fwrite(STDERR, "ERROR: Failed to parse composer.json: " . json_last_error_msg() . "\n");
    exit(1);
}

$failures = [];

$phpConstraint = $composer['require']['php'] ?? null;
if ($phpConstraint === null) {
    $failures[] = 'No PHP constraint found in composer.json require section.';
} else {
    if (strpos($phpConstraint, '8.3') === false && strpos($phpConstraint, '8.4') === false) {
        $failures[] = "PHP constraint '{$phpConstraint}' does not include ^8.3 or ^8.4.";
    }
}

$frameworkConstraint = $composer['require']['laravel/framework'] ?? null;
if ($frameworkConstraint === null) {
    $failures[] = 'No laravel/framework constraint found in composer.json require section.';
} else {
    $major = 0;
    if (preg_match('/^(\d+)/', $frameworkConstraint, $matches)) {
        $major = (int) $matches[1];
    } elseif (preg_match('/\^(\d+)/', $frameworkConstraint, $matches)) {
        $major = (int) $matches[1];
    } elseif (preg_match('/~(\d+)/', $frameworkConstraint, $matches)) {
        $major = (int) $matches[1];
    }

    if ($major < 13) {
        $failures[] = "laravel/framework constraint '{$frameworkConstraint}' is below 13.x (resolved major: {$major}).";
    }
}

$lockPath = dirname($composerPath) . '/composer.lock';
if (file_exists($lockPath)) {
    $lock = json_decode(file_get_contents($lockPath), true);
    if ($lock && isset($lock['packages'])) {
        foreach ($lock['packages'] as $package) {
            $name = $package['name'] ?? 'unknown';
            $require = $package['require'] ?? [];
            $phpReq = $require['php'] ?? null;

            if ($phpReq === null) {
                continue;
            }

            if (preg_match('/^<\s*8/', $phpReq) || preg_match('/^[~^]?[567]\./', $phpReq)) {
                $failures[] = "Package '{$name}' has PHP constraint '{$phpReq}' incompatible with 8.3/8.4.";
            }
        }
    }
}

echo "=== End-of-Life Stack Assertion ===\n";
echo "PHP constraint:      " . ($phpConstraint ?? 'MISSING') . "\n";
echo "Framework constraint: " . ($frameworkConstraint ?? 'MISSING') . "\n";
echo "Lockfile present:    " . (file_exists($lockPath) ? 'yes' : 'no') . "\n";
echo "\n";

if (count($failures) === 0) {
    echo "PASS: All EOL assertions satisfied.\n";
    echo "  - PHP: {$phpConstraint}\n";
    echo "  - Framework: laravel/framework {$frameworkConstraint}\n";
    echo "  - Target: Laravel 13 on PHP 8.3/8.4 (bug fixes through 2027, security through 2028)\n";
    exit(0);
}

echo "FAIL: " . count($failures) . " assertion(s) failed:\n";
foreach ($failures as $i => $failure) {
    echo "  " . ($i + 1) . ". {$failure}\n";
}
exit(1);
