<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Zero-dependency logical database backup: writes a plain .sql dump (DROP +
 * CREATE + INSERT statements) using nothing but PDO/Eloquent, since no
 * backup package (e.g. spatie/laravel-backup) or `mysqldump` binary is
 * assumed to be available. Files live under storage/app/backups — a private,
 * non-web-accessible directory (never public/uploads, never the public disk),
 * since a database dump can contain sensitive customer data and must not be
 * reachable by a guessable URL.
 */
class BackupService
{
    private const FILENAME_PATTERN = '/^backup-\d{4}-\d{2}-\d{2}-\d{6}\.sql$/';

    public function directory(): string
    {
        $path = storage_path('app/backups');

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        return $path;
    }

    /**
     * @return Collection<int, array{name: string, size: string, created_at: \Illuminate\Support\Carbon}>
     */
    public function list(): Collection
    {
        $files = glob($this->directory().DIRECTORY_SEPARATOR.'backup-*.sql') ?: [];

        return collect($files)
            ->map(fn ($path) => [
                'name' => basename($path),
                'size' => $this->formatBytes(filesize($path)),
                'created_at' => \Illuminate\Support\Carbon::createFromTimestamp(filemtime($path)),
            ])
            ->sortByDesc('created_at')
            ->values();
    }

    public function create(): string
    {
        $filename = 'backup-'.now()->format('Y-m-d-His').'.sql';
        $path = $this->directory().DIRECTORY_SEPARATOR.$filename;

        $handle = fopen($path, 'w');
        fwrite($handle, "-- {$filename}\n-- Generated ".now()->toDateTimeString()."\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

        foreach ($this->tableNames() as $table) {
            $this->writeTable($handle, $table);
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);

        return $filename;
    }

    /**
     * Resolves a requested filename to a safe, existing path within the
     * backups directory — never trusting the input as a path fragment.
     */
    public function resolvePath(string $filename): ?string
    {
        $safeName = basename($filename);

        if (! preg_match(self::FILENAME_PATTERN, $safeName)) {
            return null;
        }

        $path = $this->directory().DIRECTORY_SEPARATOR.$safeName;

        return is_file($path) ? $path : null;
    }

    public function delete(string $filename): bool
    {
        $path = $this->resolvePath($filename);

        return $path ? unlink($path) : false;
    }

    /**
     * @return array<int, string>
     */
    private function tableNames(): array
    {
        $dbName = DB::connection()->getDatabaseName();
        $key = "Tables_in_{$dbName}";

        return collect(DB::select('SHOW TABLES'))
            ->map(fn ($row) => $row->{$key})
            ->all();
    }

    /**
     * @param  resource  $handle
     */
    private function writeTable($handle, string $table): void
    {
        $createRow = DB::select("SHOW CREATE TABLE `{$table}`")[0];
        $createSql = $createRow->{'Create Table'};

        fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n{$createSql};\n\n");

        $pdo = DB::connection()->getPdo();

        DB::table($table)->orderBy(DB::raw('1'))->chunk(200, function ($rows) use ($handle, $table, $pdo) {
            if ($rows->isEmpty()) {
                return;
            }

            $columns = array_keys((array) $rows->first());
            $columnList = '`'.implode('`,`', $columns).'`';

            $valueGroups = $rows->map(function ($row) use ($pdo) {
                $values = array_map(
                    fn ($value) => is_null($value) ? 'NULL' : $pdo->quote((string) $value),
                    (array) $row
                );

                return '('.implode(',', $values).')';
            })->implode(',');

            fwrite($handle, "INSERT INTO `{$table}` ({$columnList}) VALUES {$valueGroups};\n");
        });

        fwrite($handle, "\n");
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        $value = (float) $bytes;

        while ($value >= 1024 && $i < count($units) - 1) {
            $value /= 1024;
            $i++;
        }

        return round($value, 1).' '.$units[$i];
    }
}
