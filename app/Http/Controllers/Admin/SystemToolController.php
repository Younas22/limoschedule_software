<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\BackupService;
use App\Services\SystemHealthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SystemToolController extends Controller
{
    public function __construct(
        private readonly SystemHealthService $health,
        private readonly BackupService $backups,
    ) {
    }

    public function index(): View
    {
        return view('admin.system-tools.index', [
            'checks' => $this->health->checks(),
            'systemInfo' => $this->health->systemInfo(),
            'queue' => $this->health->queueStatus(),
            'backups' => $this->backups->list(),
            'isDownForMaintenance' => app()->isDownForMaintenance(),
            'maintenanceSecret' => app()->isDownForMaintenance()
                ? (json_decode((string) @file_get_contents(storage_path('framework/down')), true)['secret'] ?? null)
                : null,
        ]);
    }

    public function toggleMaintenance(Request $request): RedirectResponse
    {
        if (app()->isDownForMaintenance()) {
            Artisan::call('up');
            ActivityLog::record('maintenance.disabled', 'Maintenance mode turned off.', $request);

            return back()->with('status', 'Maintenance mode disabled. The site is live again.');
        }

        $secret = Str::random(32);
        Artisan::call('down', ['--secret' => $secret]);
        ActivityLog::record('maintenance.enabled', 'Maintenance mode turned on.', $request);

        return back()->with('status', "Maintenance mode enabled. Bypass link: ".url('/').'/'.$secret);
    }

    public function clearCache(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'target' => ['required', Rule::in(['all', 'config', 'route', 'view', 'application'])],
        ]);

        match ($data['target']) {
            'all' => collect(['config:clear', 'route:clear', 'view:clear', 'cache:clear'])->each(fn ($cmd) => Artisan::call($cmd)),
            'config' => Artisan::call('config:clear'),
            'route' => Artisan::call('route:clear'),
            'view' => Artisan::call('view:clear'),
            'application' => Artisan::call('cache:clear'),
        };

        ActivityLog::record('cache.cleared', ucfirst($data['target']).' cache cleared.', $request);

        return back()->with('status', ucfirst($data['target']).' cache cleared successfully.');
    }

    public function createBackup(Request $request): RedirectResponse
    {
        $filename = $this->backups->create();

        ActivityLog::record('backup.created', "Database backup created: {$filename}", $request);

        return back()->with('status', "Backup \"{$filename}\" created successfully.");
    }

    public function downloadBackup(string $filename): BinaryFileResponse
    {
        $path = $this->backups->resolvePath($filename);

        abort_unless($path, 404);

        return response()->download($path);
    }

    public function destroyBackup(Request $request, string $filename): RedirectResponse
    {
        $deleted = $this->backups->delete($filename);

        if ($deleted) {
            ActivityLog::record('backup.deleted', "Database backup deleted: {$filename}", $request);
        }

        return back()->with('status', $deleted ? 'Backup deleted.' : 'Backup not found.');
    }

    public function activityLogs(Request $request): View
    {
        $logs = ActivityLog::with('admin')
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->query('action')))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $actions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('admin.system-tools.activity-logs', compact('logs', 'actions'));
    }

    public function errorLogs(): View
    {
        return view('admin.system-tools.error-logs', [
            'entries' => $this->parseErrorLog(),
            'logSize' => file_exists(storage_path('logs/laravel.log'))
                ? round(filesize(storage_path('logs/laravel.log')) / 1024, 1)
                : 0,
        ]);
    }

    public function clearErrorLog(Request $request): RedirectResponse
    {
        $path = storage_path('logs/laravel.log');

        if (file_exists($path)) {
            file_put_contents($path, '');
        }

        ActivityLog::record('logs.cleared', 'Error log cleared.', $request);

        return back()->with('status', 'Error log cleared successfully.');
    }

    public function clearFailedJobs(Request $request): RedirectResponse
    {
        $count = DB::table('failed_jobs')->count();
        DB::table('failed_jobs')->truncate();

        ActivityLog::record('queue.failed_cleared', "Cleared {$count} failed job(s).", $request);

        return back()->with('status', "Cleared {$count} failed job(s).");
    }

    /**
     * @return array<int, array{level: string, timestamp: string, message: string}>
     */
    private function parseErrorLog(): array
    {
        $path = storage_path('logs/laravel.log');

        if (! file_exists($path)) {
            return [];
        }

        $maxBytes = 2 * 1024 * 1024;
        $size = filesize($path);

        $handle = fopen($path, 'r');
        if ($size > $maxBytes) {
            fseek($handle, $size - $maxBytes);
        }
        $contents = stream_get_contents($handle);
        fclose($handle);

        preg_match_all(
            '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.*?)(?=^\[\d{4}-\d{2}-\d{2}|\z)/ms',
            $contents,
            $matches,
            PREG_SET_ORDER
        );

        $entries = collect($matches)
            ->map(fn ($match) => [
                'timestamp' => $match[1],
                'level' => strtoupper($match[3]),
                'message' => Str::limit(trim(explode("\n", $match[4])[0]), 300),
            ])
            ->reverse()
            ->take(100)
            ->values()
            ->all();

        return $entries;
    }
}
