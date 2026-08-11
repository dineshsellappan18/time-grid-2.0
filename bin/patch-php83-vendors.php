#!/usr/bin/env php
<?php
/**
 * WO-015 — re-apply PHP 8.3/8.4 compatibility patches on vendors after composer install.
 */
$methods = [
    'offsetExists','offsetGet','offsetSet','offsetUnset',
    'current','key','next','rewind','valid',
    'count','getIterator','jsonSerialize','serialize','unserialize',
    'hasChildren','getChildren','accept','getInnerIterator',
    'open','close','read','write','destroy','gc',
    '__set_state','__sleep','__wakeup','__serialize','__unserialize',
];

$roots = [
    __DIR__ . '/../vendor/symfony',
    __DIR__ . '/../vendor/swiftmailer',
    __DIR__ . '/../vendor/doctrine',
];

$patched = 0;
foreach ($roots as $root) {
    if (!is_dir($root)) continue;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
    foreach ($it as $file) {
        if (!$file->isFile() || $file->getExtension() !== 'php') continue;
        $path = $file->getPathname();
        $text = file_get_contents($path);
        $original = $text;
        foreach ($methods as $method) {
            $pattern = '/^([ \t]*)((?:public|protected|private|final|static)\s+)*function\s+'
                . preg_quote($method, '/') . '\s*\(/m';
            $text = preg_replace_callback($pattern, function ($m) use (&$text) {
                $pos = strpos($text, $m[0]);
                $before = rtrim(substr($text, 0, $pos === false ? 0 : $pos));
                $lines = $before === '' ? [] : preg_split("/\n/", $before);
                $recent = implode("\n", array_slice($lines, -3));
                if (strpos($recent, 'ReturnTypeWillChange') !== false) {
                    return $m[0];
                }
                return $m[1] . "#[\\ReturnTypeWillChange]\n" . $m[0];
            }, $text);
        }
        if ($text !== $original) {
            file_put_contents($path, $text);
            $patched++;
        }
    }
}

// Carbon 1.x PHP 8.x soft fixes (do not re-attribute; attributes applied once in committed vendor patch path)
$carbon = __DIR__ . '/../vendor/nesbot/carbon/src/Carbon/Carbon.php';
if (is_file($carbon)) {
    $t = file_get_contents($carbon);
    if (strpos($t, 'if (!is_array($lastErrors))') === false) {
        $t = str_replace(
            'private static function setLastErrors(array $lastErrors)
    {
        static::$lastErrors = $lastErrors;
    }',
            "private static function setLastErrors(\$lastErrors)
    {
        if (!is_array(\$lastErrors)) {
            \$lastErrors = [
                'warning_count' => 0,
                'warnings' => [],
                'error_count' => 0,
                'errors' => [],
            ];
        }

        static::\$lastErrors = \$lastErrors;
    }",
            $t
        );
    }
    if (strpos($t, "if (\$time === null || \$time === '')") === false) {
        $t = str_replace(
            "public static function hasRelativeKeywords(\$time)
    {
        if (strtotime(\$time) === false) {",
            "public static function hasRelativeKeywords(\$time)
    {
        if (\$time === null || \$time === '') {
            return false;
        }

        if (strtotime(\$time) === false) {",
            $t
        );
    }
    $t = str_replace(
        'static::setLastErrors(parent::getLastErrors());',
        "\$__carbonLastErrors = parent::getLastErrors();
        static::setLastErrors(is_array(\$__carbonLastErrors) ? \$__carbonLastErrors : array(
            'warning_count' => 0,
            'warnings' => array(),
            'error_count' => 0,
            'errors' => array(),
        ));",
        $t
    );
    file_put_contents($carbon, $t);
    echo "patched carbon soft-fixes\n";
}

$dotenv = __DIR__ . '/../vendor/vlucas/phpdotenv/src/Loader.php';
if (is_file($dotenv) && strpos(file_get_contents($dotenv), 'auto_detect_line_endings') !== false) {
    $t = file_get_contents($dotenv);
    $t = preg_replace(
        '/protected function readLinesFromFile\(\$filePath\)\s*\{.*?return \$lines;\s*\}/s',
        "protected function readLinesFromFile(\$filePath)\n    {\n        return file(\$filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);\n    }",
        $t
    );
    file_put_contents($dotenv, $t);
    echo "patched phpdotenv\n";
}

echo "vendor attribute files touched: {$patched}\n";
