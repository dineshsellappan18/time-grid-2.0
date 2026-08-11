<?php

use App\Bootstrap\ApplicationKeyGuard;

class ApplicationKeyGuardTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_allows_local_environment_with_placeholder_key()
    {
        $this->expectNotToPerformAssertions();

        $guard = new ApplicationKeyGuard();

        $guard->assertSecureKey('local', ApplicationKeyGuard::PLACEHOLDER_KEY);
        $guard->assertSecureKey('local', '');
        $guard->assertSecureKey('testing', null);
    }

    /** @test */
    public function it_allows_non_local_environment_with_a_real_key()
    {
        $this->expectNotToPerformAssertions();

        $guard = new ApplicationKeyGuard();

        $guard->assertSecureKey('production', 'base64:dGVzdC1rZXktZm9yLXVuaXQtdGVzdHM=');
        $guard->assertSecureKey('staging', 'SomeGeneratedApplicationKeyValue123');
    }

    /** @test */
    public function it_rejects_empty_key_outside_local()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Application key is missing or still a placeholder');

        (new ApplicationKeyGuard())->assertSecureKey('production', '');
    }

    /** @test */
    public function it_rejects_placeholder_key_outside_local()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Application key is missing or still a placeholder');

        (new ApplicationKeyGuard())->assertSecureKey('production', ApplicationKeyGuard::PLACEHOLDER_KEY);
    }

    /** @test */
    public function it_rejects_null_key_in_staging()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Application key is missing or still a placeholder');

        (new ApplicationKeyGuard())->assertSecureKey('staging', null);
    }

    /** @test */
    public function it_detects_missing_and_placeholder_keys()
    {
        $guard = new ApplicationKeyGuard();

        $this->assertTrue($guard->isMissingOrPlaceholder(null));
        $this->assertTrue($guard->isMissingOrPlaceholder(''));
        $this->assertTrue($guard->isMissingOrPlaceholder('  '));
        $this->assertTrue($guard->isMissingOrPlaceholder(ApplicationKeyGuard::PLACEHOLDER_KEY));
        $this->assertFalse($guard->isMissingOrPlaceholder('base64:abc123'));
        $this->assertFalse($guard->isMissingOrPlaceholder('xlhF31NeOlibJcoOW9tvZg7TkHcAZI3a'));
    }
}
