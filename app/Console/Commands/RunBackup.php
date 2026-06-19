<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RunBackup extends Command
{
    protected $signature = 'backup:run';
    protected $description = 'Create a SQL backup of the database';

    public function handle()
    {
        $filename = 'backup-' . now()->format('Y-m-d-H-i-s') . '.sql';
        $path = 'backups/' . $filename;

        $tables = DB::select('SHOW TABLES');
        $sql = '';

        foreach ($tables as $table) {
            $tableName = reset($table);

            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $sql .= "\n\n-- Structure for `{$tableName}`\n\n";
            $sql .= $createTable[0]->{'Create Table'} . ";\n\n";

            $rows = DB::table($tableName)->get();
            if ($rows->count() > 0) {
                $sql .= "-- Data for `{$tableName}`\n\n";
                foreach ($rows as $row) {
                    $row = (array) $row;
                    $columns = implode('`, `', array_keys($row));
                    $values = implode("', '", array_map(function ($v) {
                        return str_replace("'", "\\'", $v ?? 'NULL');
                    }, $row));
                    $sql .= "INSERT INTO `{$tableName}` (`{$columns}`) VALUES ('{$values}');\n";
                }
            }
        }

        Storage::disk('local')->put($path, $sql);

        $this->info("Backup created: {$path}");

        return 0;
    }
}
