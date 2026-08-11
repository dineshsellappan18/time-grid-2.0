<?php

use App\Console\Commands\AutopublishBusinessVacancies;
use App\Console\Commands\SendBusinessReport;
use Symfony\Component\Console\Input\InputArgument;

class CommandArgumentDefinitionTest extends TestCase
{
    /** @test */
    public function send_business_report_defines_optional_business_argument()
    {
        $command = $this->app->make(SendBusinessReport::class);
        $method = new ReflectionMethod($command, 'getArguments');
        $method->setAccessible(true);
        $arguments = $method->invoke($command);

        $this->assertSame('business', $arguments[0][0]);
        $this->assertSame(InputArgument::OPTIONAL, $arguments[0][1]);
        $this->assertInternalType('string', $arguments[0][2]);
    }

    /** @test */
    public function autopublish_vacancies_defines_optional_business_argument()
    {
        $command = $this->app->make(AutopublishBusinessVacancies::class);
        $method = new ReflectionMethod($command, 'getArguments');
        $method->setAccessible(true);
        $arguments = $method->invoke($command);

        $this->assertSame('business', $arguments[0][0]);
        $this->assertSame(InputArgument::OPTIONAL, $arguments[0][1]);
        $this->assertInternalType('string', $arguments[0][2]);
    }
}
