<?php

abstract class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    protected $baseUrl = 'http://localhost:8000';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        $this->assertTestingDatabaseConfigured($app);

        return $app;
    }

    /**
     * Fail loudly when the testing connection is misconfigured (WO-003).
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function assertTestingDatabaseConfigured($app)
    {
        if (env('APP_ENV') !== 'testing') {
            return;
        }

        $config = $app['config']->get('database.connections.testing');
        $missing = [];
        foreach (['host', 'database', 'username'] as $key) {
            if (empty($config[$key])) {
                $missing[] = $key;
            }
        }

        if (!empty($missing)) {
            throw new RuntimeException(
                'Testing database is not configured (missing: '.implode(', ', $missing).'). '
                .'Copy .env.testing.example to .env.testing and set TEST_DB_* values, '
                .'or rely on phpunit.xml env defaults.'
            );
        }
    }

    public function prepareForTests()
    {
        //
    }

    public function setupDatabase()
    {
        //
    }

    public function setUp()
    {
        parent::setUp();

        $this->prepareForTests();
    }

    public function tearDown()
    {
        parent::tearDown();

        Mockery::close();
    }
}
