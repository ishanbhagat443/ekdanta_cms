<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

/**
 * Nightly MySQL dump to storage/app/backups (gzip when available),
 * pruning anything older than the configured retention window.
 * Scheduled daily in Console\Kernel; run manually via `php artisan gsm:backup`.
 */
class BackupDatabase extends Command
{
    protected $signature = 'gsm:backup {--keep=14 : Days of backups to retain}';

    protected $description = 'Dump the database to storage/app/backups and prune old dumps';

    public function handle(): int
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (($config['driver'] ?? null) !== 'mysql') {
            $this->warn("Backup skipped: driver '{$config['driver']}' not supported (mysql only).");

            return self::SUCCESS;
        }

        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);

        $file = $dir.DIRECTORY_SEPARATOR.$config['database'].'-'.now()->format('Y-m-d_His').'.sql';

        $command = [
            $this->mysqldumpBinary(),
            '--host='.($config['host'] ?? '127.0.0.1'),
            '--port='.($config['port'] ?? 3306),
            '--user='.$config['username'],
            '--single-transaction',
            '--skip-lock-tables',
            '--routines',
            '--result-file='.$file,
            $config['database'],
        ];

        $process = new Process($command, null, [
            'MYSQL_PWD' => $config['password'] ?? '',
        ]);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful() || ! File::exists($file)) {
            $this->error('mysqldump failed: '.trim($process->getErrorOutput() ?: 'binary not found in PATH?'));

            return self::FAILURE;
        }

        // Gzip when the extension is available (it is on WAMP + most servers).
        if (function_exists('gzencode')) {
            File::put($file.'.gz', gzencode((string) File::get($file), 9));
            File::delete($file);
            $file .= '.gz';
        }

        $this->info('Backup written: '.$file.' ('.round(File::size($file) / 1024).' KB)');

        $this->prune($dir, (int) $this->option('keep'));

        return self::SUCCESS;
    }

    /**
     * MYSQLDUMP_PATH env wins; otherwise PATH; otherwise probe the WAMP
     * install this project develops on.
     */
    private function mysqldumpBinary(): string
    {
        if ($configured = env('MYSQLDUMP_PATH')) {
            return $configured;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            foreach (glob('C:/wamp64/bin/mysql/*/bin/mysqldump.exe') ?: [] as $candidate) {
                return $candidate;
            }
        }

        return 'mysqldump';
    }

    private function prune(string $dir, int $keepDays): void
    {
        $cutoff = now()->subDays(max(1, $keepDays))->getTimestamp();
        $removed = 0;

        foreach (File::files($dir) as $backup) {
            if ($backup->getMTime() < $cutoff) {
                File::delete($backup->getPathname());
                $removed++;
            }
        }

        if ($removed > 0) {
            $this->info("Pruned {$removed} old backup(s).");
        }
    }
}
