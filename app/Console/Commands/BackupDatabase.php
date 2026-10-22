<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'aqar:backup {--path=backups : Directory on the local disk}';

    protected $description = 'Backup the database file (sqlite) or dump instructions (mysql) into storage';

    public function handle(): int
    {
        $dir = (string) $this->option('path');
        Storage::disk('local')->makeDirectory($dir);
        $stamp = now()->format('Ymd-His');
        $connection = config('database.default');

        if ($connection === 'sqlite') {
            $source = config('database.connections.sqlite.database');
            $target = "{$dir}/database-{$stamp}.sqlite";
            Storage::disk('local')->put($target, file_get_contents($source));
            $this->info("SQLite backup written to storage/app/{$target}");

            return self::SUCCESS;
        }

        // MySQL: shell out only when mysqldump exists; never print credentials.
        $db = config('database.connections.mysql.database');
        $target = storage_path("app/{$dir}/database-{$stamp}.sql");
        $command = sprintf('mysqldump --single-transaction %s > %s 2>&1', escapeshellarg((string) $db), escapeshellarg($target));
        exec($command, $output, $code);
        if ($code !== 0) {
            $this->error('mysqldump failed. Verify mysqldump is installed and DB credentials env (MYSQL_PWD) are set.');

            return self::FAILURE;
        }
        $this->info("MySQL backup written to {$target}");

        return self::SUCCESS;
    }
}
