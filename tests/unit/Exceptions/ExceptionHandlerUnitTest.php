<?php

use App\Exceptions\Handler;
use Illuminate\Support\Facades\Log;

class ExceptionHandlerUnitTest extends TestCase
{
    /**
     * @test
     */
    public function it_logs_reportable_exceptions_through_the_application_logger()
    {
        $writer = Mockery::mock('Illuminate\Log\Writer');
        $writer->shouldReceive('error')
            ->once()
            ->with(
                'handler-boom',
                Mockery::on(function ($context) {
                    return is_array($context)
                        && isset($context['exception'], $context['correlation_id'], $context['file'], $context['line'])
                        && $context['exception'] === 'Exception';
                })
            );
        $writer->shouldReceive('useFiles')->zeroOrMoreTimes();
        $writer->shouldReceive('useDailyFiles')->zeroOrMoreTimes();
        Log::swap($writer);

        $handler = $this->app->make(Handler::class);
        $handler->report(new Exception('handler-boom'));

        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function it_does_not_register_rollbar_when_token_env_is_present()
    {
        putenv('ROLLBAR_TOKEN=fake-token-should-be-ignored');
        config(['services.rollbar.access_token' => 'fake-token-should-be-ignored']);

        $providers = array_keys($this->app->getLoadedProviders());
        $this->assertNotContains(
            'Jenssegers\\Rollbar\\RollbarServiceProvider',
            $providers
        );
        $this->assertFalse(class_exists('Jenssegers\\Rollbar\\RollbarServiceProvider', false));
    }
}
