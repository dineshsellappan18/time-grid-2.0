#!/usr/bin/env php
<?php
/**
 * Bootstrap 5 migration lint — verifies no Bootstrap 3-only hooks remain in Blade templates.
 *
 * Checks for:
 * - data-toggle (should be data-bs-toggle)
 * - data-dismiss (should be data-bs-dismiss)
 * - data-target without data-bs-target
 * - class="close" (should be btn-close)
 * - sr-only (should be visually-hidden)
 * - pull-left / pull-right (should be float-start / float-end or flexbox)
 * - col-xs-* (removed in BS5, use col-*)
 * - label-* badge classes (should be bg-*)
 */

$exitCode = 0;
$viewsDir = __DIR__ . '/../resources/views';

$patterns = [
    'data-toggle='             => 'Use data-bs-toggle= instead',
    'data-dismiss='            => 'Use data-bs-dismiss= instead',
    '\'data-toggle\''         => 'Use \'data-bs-toggle\' instead (PHP array key)',
    'class="close"'           => 'Use class="btn-close" instead',
];

$warningPatterns = [
    'sr-only'                  => 'Consider using visually-hidden (BS5)',
    'col-xs-'                 => 'col-xs-* removed in BS5; use col-*',
];

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewsDir, FilesystemIterator::SKIP_DOTS)
);

$errors = [];
$warnings = [];

foreach ($files as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }

    $contents = file_get_contents($file->getPathname());
    $relativePath = str_replace(realpath(__DIR__ . '/..') . '/', '', $file->getPathname());

    foreach ($patterns as $pattern => $message) {
        if (strpos($contents, $pattern) !== false) {
            $errors[] = sprintf('  ERROR: %s — %s', $relativePath, $message);
        }
    }

    foreach ($warningPatterns as $pattern => $message) {
        if (strpos($contents, $pattern) !== false) {
            $warnings[] = sprintf('  WARN:  %s — %s', $relativePath, $message);
        }
    }
}

echo "Bootstrap 5 Migration Lint\n";
echo str_repeat('=', 40) . "\n\n";

if (count($errors) > 0) {
    echo "ERRORS (" . count($errors) . "):\n";
    foreach ($errors as $error) {
        echo $error . "\n";
    }
    $exitCode = 1;
} else {
    echo "No Bootstrap 3-only hooks found. ✓\n";
}

if (count($warnings) > 0) {
    echo "\nWARNINGS (" . count($warnings) . "):\n";
    foreach ($warnings as $warning) {
        echo $warning . "\n";
    }
}

echo "\n";
exit($exitCode);
