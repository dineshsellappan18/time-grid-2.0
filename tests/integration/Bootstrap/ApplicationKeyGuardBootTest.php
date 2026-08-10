<?php

use App\Bootstrap\ApplicationKeyGuard;

class ApplicationKeyGuardBootTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage Application key is missing or still a placeholder
     */
    public function it_fails_closed_when_booting_with_placeholder_key_outside_local()
    {
        putenv('APP_ENV=production');
        putenv('APP_KEY='.ApplicationKeyGuard::PLACEHOLDER_KEY);
        $_ENV['APP_ENV'] = 'production';
        $_ENV['APP_KEY'] = ApplicationKeyGuard::PLACEHOLDER_KEY;
        $_SERVER['APP_ENV'] = 'production';
        $_SERVER['APP_KEY'] = ApplicationKeyGuard::PLACEHOLDER_KEY;

        $app = require __DIR__.'/../../../bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    }

    public function tearDown()
    {
        putenv('APP_ENV=local');
        putenv('APP_KEY');
        unset($_ENV['APP_ENV'], $_ENV['APP_KEY'], $_SERVER['APP_ENV'], $_SERVER['APP_KEY']);

        parent::tearDown();
    }
}
