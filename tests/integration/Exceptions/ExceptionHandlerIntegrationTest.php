<?php

class ExceptionHandlerIntegrationTest extends TestCase
{
    /**
     * @test
     */
    public function thrown_exceptions_do_not_expose_stack_traces_when_debug_is_off()
    {
        config(['app.debug' => false]);

        \Route::get('/__test/throw-secret', function () {
            throw new Exception('SECRET_STACK_MARKER_SHOULD_NOT_LEAK');
        });

        $response = $this->call('GET', '/__test/throw-secret');

        $this->assertTrue(in_array($response->getStatusCode(), [500, 302, 200]));
        $this->assertStringNotContainsString('SECRET_STACK_MARKER_SHOULD_NOT_LEAK', $response->getContent());
        $this->assertStringNotContainsString('Stack trace', $response->getContent());
        $this->assertStringNotContainsString('rollbar', strtolower($response->getContent()));
    }
}
