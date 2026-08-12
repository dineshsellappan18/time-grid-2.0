<?php

namespace App\Http\Controllers\Root;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class RootController extends Controller
{
    public function getIndex()
    {
        logger()->info(__METHOD__);

        $users = User::all();

        return view('root.dashboard', compact('users'));
    }

    public function getConsole(Request $request)
    {
        logger()->info('root.console.view', ['actor' => auth()->id()]);

        $viewModel = $this->buildConsoleViewModel($request);

        return view('root.console', $viewModel);
    }

    public function getSudo($userId)
    {
        logger()->info(__METHOD__);
        logger()->warning("[!] ROOT SUDO userId:{$userId}");

        auth()->loginUsingId($userId);

        flash()->warning('ADVICE: THIS IS FOR AUTHORIZED USE ONLY AND YOUR ACTIONS ARE BEING RECORDERED !!!');

        return redirect()->route('user.directory.list');
    }

    private function buildConsoleViewModel(Request $request): array
    {
        return [
            'runtime'           => $this->gatherRuntimeFacts(),
            'phaseTimeline'     => $this->getPhaseTimeline(),
            'architectureMetrics' => $this->getArchitectureMetrics(),
            'supplyChain'       => $this->getSupplyChainStatus(),
            'hotPathQueries'    => $this->getHotPathQueryCounts(),
            'guardMode'         => config('ical.guard_mode', 'shadow'),
            'auditTrail'        => $this->getAuditTrail($request),
        ];
    }

    private function gatherRuntimeFacts(): array
    {
        $facts = [
            'php' => [
                'version' => PHP_VERSION,
                'status' => 'ok',
            ],
            'laravel' => [
                'version' => app()->version(),
                'status' => 'ok',
            ],
            'node' => $this->getNodeBuildVersion(),
            'mysql' => $this->getMySqlStatus(),
            'redis' => $this->getRedisStatus(),
        ];

        return $facts;
    }

    private function getNodeBuildVersion(): array
    {
        $manifestPath = public_path('build/manifest.json');

        if (!file_exists($manifestPath)) {
            return ['version' => 'unknown', 'status' => 'unavailable', 'message' => 'Manifest not found'];
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $entryCount = is_array($manifest) ? count($manifest) : 0;

        return [
            'version' => "Vite ({$entryCount} entries)",
            'status' => 'ok',
            'built_at' => date('Y-m-d H:i:s', filemtime($manifestPath)),
        ];
    }

    private function getMySqlStatus(): array
    {
        try {
            $version = DB::selectOne('SELECT VERSION() as v');
            return [
                'version' => $version->v ?? 'unknown',
                'status' => 'ok',
            ];
        } catch (\Exception $e) {
            return [
                'version' => 'unreachable',
                'status' => 'error',
                'message' => 'Connection failed',
            ];
        }
    }

    private function getRedisStatus(): array
    {
        try {
            $info = Redis::info();
            $version = $info['redis_version'] ?? ($info['Server']['redis_version'] ?? 'unknown');
            return [
                'version' => $version,
                'status' => 'ok',
            ];
        } catch (\Exception $e) {
            return [
                'version' => 'unreachable',
                'status' => 'error',
                'message' => 'Connection failed',
            ];
        }
    }

    private function getPhaseTimeline(): array
    {
        return [
            ['phase' => 1, 'name' => 'Foundation & Security',  'status' => 'completed', 'exit_date' => '2026-07-15'],
            ['phase' => 2, 'name' => 'Framework Upgrade',       'status' => 'completed', 'exit_date' => '2026-07-20'],
            ['phase' => 3, 'name' => 'Data & Authorization',    'status' => 'completed', 'exit_date' => '2026-07-28'],
            ['phase' => 4, 'name' => 'Performance & Caching',   'status' => 'completed', 'exit_date' => '2026-08-05'],
            ['phase' => 5, 'name' => 'Frontend & UI Migration', 'status' => 'in_progress', 'exit_date' => null],
        ];
    }

    private function getArchitectureMetrics(): array
    {
        $baselinePath = base_path('docs/modernization/architecture-baseline.json');

        if (!file_exists($baselinePath)) {
            return [
                'available' => false,
                'message' => 'Baseline not published yet',
                'last_updated' => null,
            ];
        }

        $baseline = json_decode(file_get_contents($baselinePath), true);

        return [
            'available' => true,
            'last_updated' => date('Y-m-d H:i:s', filemtime($baselinePath)),
            'layer_violations' => $baseline['layer_violations'] ?? 0,
            'cycles' => $baseline['cycles'] ?? 0,
            'phpstan_level' => $baseline['phpstan_level'] ?? 'unknown',
            'coverage' => $baseline['coverage'] ?? 'unknown',
        ];
    }

    private function getSupplyChainStatus(): array
    {
        $composerLock = base_path('composer.lock');
        $packageLock = base_path('package-lock.json');

        $constraints = 0;
        $advisories = 0;

        if (file_exists($composerLock)) {
            $lock = json_decode(file_get_contents($composerLock), true);
            $constraints = count($lock['packages'] ?? []);
        }

        return [
            'php_packages' => $constraints,
            'advisories' => $advisories,
            'node_available' => file_exists($packageLock),
        ];
    }

    private function getHotPathQueryCounts(): array
    {
        $baselinePath = base_path('docs/modernization/architecture-baseline.json');

        if (!file_exists($baselinePath)) {
            return ['available' => false, 'message' => 'Baseline not published yet'];
        }

        $baseline = json_decode(file_get_contents($baselinePath), true);

        return [
            'available' => true,
            'agenda_index' => $baseline['hot_paths']['agenda_index'] ?? 'N/A',
            'ical_feed' => $baseline['hot_paths']['ical_feed'] ?? 'N/A',
            'booking_availability' => $baseline['hot_paths']['booking_availability'] ?? 'N/A',
        ];
    }

    private function getAuditTrail(Request $request): array
    {
        $query = DB::table('audit_logs')
            ->select(['occurred_at', 'actor_id', 'resource_type', 'action', 'correlation_id', 'outcome'])
            ->orderBy('occurred_at', 'desc');

        if ($request->filled('filter_actor')) {
            $query->where('actor_id', $request->input('filter_actor'));
        }
        if ($request->filled('filter_action')) {
            $query->where('action', 'like', '%' . $request->input('filter_action') . '%');
        }
        if ($request->filled('filter_resource')) {
            $query->where('resource_type', $request->input('filter_resource'));
        }
        if ($request->filled('filter_outcome')) {
            $query->where('outcome', $request->input('filter_outcome'));
        }

        $paginated = $query->paginate(25);

        return [
            'entries' => $paginated->items(),
            'pagination' => $paginated,
            'filters' => [
                'actor' => $request->input('filter_actor', ''),
                'action' => $request->input('filter_action', ''),
                'resource' => $request->input('filter_resource', ''),
                'outcome' => $request->input('filter_outcome', ''),
            ],
        ];
    }
}
