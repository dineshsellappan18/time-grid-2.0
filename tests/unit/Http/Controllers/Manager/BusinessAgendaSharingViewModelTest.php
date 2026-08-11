<?php

namespace Tests\Unit\Http\Controllers\Manager;

use Tests\TestCase;
use App\Http\Controllers\Manager\BusinessAgendaController;
use App\TG\ICalTokenService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use ReflectionMethod;
use Timegridio\Concierge\Concierge;

class BusinessAgendaSharingViewModelTest extends TestCase
{
    private BusinessAgendaController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $concierge = $this->createMock(Concierge::class);
        $tokenService = $this->createMock(ICalTokenService::class);
        $this->controller = new BusinessAgendaController($concierge, $tokenService);
    }

    public function test_view_model_with_active_token(): void
    {
        $business = $this->createBusinessStub(1);

        DB::table('ical_tokens')->insert([
            'business_id' => 1,
            'token_hash' => hash('sha256', 'test-token'),
            'rotated_at' => '2026-08-01 10:00:00',
            'last_used_at' => '2026-08-10 14:30:00',
            'created_at' => '2026-07-15 09:00:00',
            'updated_at' => '2026-08-01 10:00:00',
        ]);

        $method = new ReflectionMethod($this->controller, 'buildSharingViewModel');
        $method->setAccessible(true);
        $viewModel = $method->invoke($this->controller, $business);

        $this->assertTrue($viewModel['hasToken']);
        $this->assertNotNull($viewModel['maskedUrl']);
        $this->assertStringContains('••••', $viewModel['maskedUrl']);
        $this->assertNotNull($viewModel['tokenMetadata']);
        $this->assertEquals('SHA-256 (hashed)', $viewModel['tokenMetadata']['storage']);
        $this->assertIsArray($viewModel['authorizationMatrix']);
        $this->assertCount(3, $viewModel['authorizationMatrix']);
    }

    public function test_view_model_without_token(): void
    {
        $business = $this->createBusinessStub(99);

        $method = new ReflectionMethod($this->controller, 'buildSharingViewModel');
        $method->setAccessible(true);
        $viewModel = $method->invoke($this->controller, $business);

        $this->assertFalse($viewModel['hasToken']);
        $this->assertNull($viewModel['maskedUrl']);
        $this->assertNull($viewModel['tokenMetadata']);
    }

    public function test_view_model_with_no_denials(): void
    {
        $business = $this->createBusinessStub(1);

        $method = new ReflectionMethod($this->controller, 'getDenialLog');
        $method->setAccessible(true);
        $log = $method->invoke($this->controller, $business);

        $this->assertIsArray($log);
    }

    public function test_view_model_with_revoked_token_only(): void
    {
        $business = $this->createBusinessStub(2);

        DB::table('ical_tokens')->insert([
            'business_id' => 2,
            'token_hash' => hash('sha256', 'old-token'),
            'rotated_at' => '2026-07-01 10:00:00',
            'revoked_at' => '2026-08-01 10:00:00',
            'created_at' => '2026-06-01 09:00:00',
            'updated_at' => '2026-08-01 10:00:00',
        ]);

        $method = new ReflectionMethod($this->controller, 'buildSharingViewModel');
        $method->setAccessible(true);
        $viewModel = $method->invoke($this->controller, $business);

        $this->assertFalse($viewModel['hasToken']);
        $this->assertNull($viewModel['maskedUrl']);
    }

    public function test_divergence_count(): void
    {
        $business = $this->createBusinessStub(3);

        DB::table('audit_logs')->insert([
            ['entity_type' => 'ical_feed', 'entity_id' => '3', 'action' => 'guard_divergence', 'actor_id' => 1, 'correlation_id' => 'test-1', 'context' => '{}', 'created_at' => now()],
            ['entity_type' => 'ical_feed', 'entity_id' => '3', 'action' => 'guard_divergence', 'actor_id' => 1, 'correlation_id' => 'test-2', 'context' => '{}', 'created_at' => now()],
        ]);

        $method = new ReflectionMethod($this->controller, 'getDivergenceCount');
        $method->setAccessible(true);
        $count = $method->invoke($this->controller, $business);

        $this->assertEquals(2, $count);
    }

    private function createBusinessStub(int $id): object
    {
        return (object) ['id' => $id, 'slug' => 'test-business-' . $id];
    }

    private function assertStringContains(string $needle, string $haystack): void
    {
        $this->assertTrue(
            str_contains($haystack, $needle),
            "Failed asserting that '{$haystack}' contains '{$needle}'"
        );
    }
}
