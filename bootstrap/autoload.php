<?php

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Register The Composer Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so that we do not have to worry about the
| loading of any our classes "manually". Feels great to relax.
|
*/

require __DIR__.'/../vendor/autoload.php';

// WO-015 — PHPUnit 10 aliases for Laravel 5.3 testing concerns.
$phpunitAliases = [
    'PHPUnit_Framework_TestCase' => \PHPUnit\Framework\TestCase::class,
    'PHPUnit_Framework_Assert' => \PHPUnit\Framework\Assert::class,
    'PHPUnit_Framework_Constraint' => \PHPUnit\Framework\Constraint\Constraint::class,
    'PHPUnit_Framework_ExpectationFailedException' => \PHPUnit\Framework\ExpectationFailedException::class,
];
foreach ($phpunitAliases as $legacy => $modern) {
    if (!class_exists($legacy, false) && class_exists($modern)) {
        class_alias($modern, $legacy);
    }
}

/*
|--------------------------------------------------------------------------
| Include The Compiled Class File
|--------------------------------------------------------------------------
|
| To dramatically increase your application's performance, you may use a
| compiled class file which contains all of the classes commonly used
| by a request. The Artisan "optimize" is used to create this file.
|
*/

$compiledPath = __DIR__.'/cache/compiled.php';

if (file_exists($compiledPath)) {
    require $compiledPath;
}
