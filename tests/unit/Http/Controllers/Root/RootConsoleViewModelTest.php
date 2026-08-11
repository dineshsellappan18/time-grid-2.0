<?php

namespace Tests\Unit\Http\Controllers\Root;

use Tests\TestCase;
use App\Http\Controllers\Root\RootController;
use Illuminate\Http\Request;
use ReflectionMethod;

class RootConsoleViewModelTest extends TestCase
{
    private RootController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new RootController();
    }

    public function test_runtime_facts_returns_php_version(): void
    {
        $method = new ReflectionMethod($this->controller, 'gatherRuntimeFacts');
        $method->setAccessible(true);
        $facts = $method->invoke($this->controller);

        $this->assertArrayHasKey('php', $facts);
        $this->assertEquals(PHP_VERSION, $facts['php']['version']);
        $this->assertEquals('ok', $facts['php']['status']);
    }

    public function test_runtime_facts_returns_laravel_version(): void
    {
        $method = new ReflectionMethod($this->controller, 'gatherRuntimeFacts');
        $method->setAccessible(true);
        $facts = $method->invoke($this->controller);

        $this->assertArrayHasKey('laravel', $facts);
        $this->assertEquals('ok', $facts['laravel']['status']);
    }

    public function test_node_build_version_when_manifest_missing(): void
    {
        $method = new ReflectionMethod($this->controller, 'getNodeBuildVersion');
        $method->setAccessible(true);
        $result = $method->invoke($this->controller);

        $this->assertArrayHasKey('status', $result);
    }

    public function test_architecture_metrics_when_baseline_exists(): void
    {
        $method = new ReflectionMethod($this->controller, 'getArchitectureMetrics');
        $method->setAccessible(true);
        $metrics = $method->invoke($this->controller);

        $this->assertTrue($metrics['available']);
        $this->assertArrayHasKey('layer_violations', $metrics);
        $this->assertArrayHasKey('cycles', $metrics);
        $this->assertArrayHasKey('phpstan_level', $metrics);
    }

    public function test_phase_timeline_returns_all_phases(): void
    {
        $method = new ReflectionMethod($this->controller, 'getPhaseTimeline');
        $method->setAccessible(true);
        $timeline = $method->invoke($this->controller);

        $this->assertCount(5, $timeline);
        $this->assertEquals('completed', $timeline[0]['status']);
    }

    public function test_hot_path_queries_when_baseline_exists(): void
    {
        $method = new ReflectionMethod($this->controller, 'getHotPathQueryCounts');
        $method->setAccessible(true);
        $counts = $method->invoke($this->controller);

        $this->assertTrue($counts['available']);
        $this->assertArrayHasKey('agenda_index', $counts);
        $this->assertArrayHasKey('ical_feed', $counts);
    }

    public function test_audit_trail_returns_paginated_entries(): void
    {
        $request = Request::create('/root/console', 'GET');
        $method = new ReflectionMethod($this->controller, 'getAuditTrail');
        $method->setAccessible(true);
        $trail = $method->invoke($this->controller, $request);

        $this->assertArrayHasKey('entries', $trail);
        $this->assertArrayHasKey('pagination', $trail);
        $this->assertArrayHasKey('filters', $trail);
    }

    public function test_audit_trail_filters_by_action(): void
    {
        $request = Request::create('/root/console', 'GET', ['filter_action' => 'rotate']);
        $method = new ReflectionMethod($this->controller, 'getAuditTrail');
        $method->setAccessible(true);
        $trail = $method->invoke($this->controller, $request);

        $this->assertEquals('rotate', $trail['filters']['action']);
    }

    public function test_mysql_status_graceful_on_failure(): void
    {
        $method = new ReflectionMethod($this->controller, 'getMySqlStatus');
        $method->setAccessible(true);
        $status = $method->invoke($this->controller);

        $this->assertArrayHasKey('status', $status);
        $this->assertArrayHasKey('version', $status);
    }

    public function test_redis_status_graceful_on_failure(): void
    {
        $method = new ReflectionMethod($this->controller, 'getRedisStatus');
        $method->setAccessible(true);
        $status = $method->invoke($this->controller);

        $this->assertArrayHasKey('status', $status);
        $this->assertArrayHasKey('version', $status);
    }
}
