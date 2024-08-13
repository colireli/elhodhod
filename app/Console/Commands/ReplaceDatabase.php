<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema; 


class ReplaceDatabase extends Command
{
    protected $signature = 'replace:database';

    protected $description = 'Replace the database and update storage link';

    public function handle()
    {
        try {

            // Check if DEMO_MODE is On
            if (env('DEMO_MODE') !== 'On') {
                $this->error('DEMO_MODE is not set to On. Aborting the operation.');
                return;
            }
            
            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Drop existing tables
            $this->dropTables();

            // Enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            // Import data from cargo.sql
            $this->importDataFromSqlFile();

            // Delete public\storage
            $this->deletePublicStorage();

            // Run php artisan storage:link
            $this->linkPublicStorage();

            // Run php artisan refresh:cache
            $this->call('refresh:cache');


            $this->info('Database replaced and storage link updated successfully.');
        } catch (\Exception $e) {
            $this->error('An error occurred: '.$e->getMessage());
            $this->displayErrorDetails($e);
        }
    }

    protected function dropTables()
    {
        try {
            $tables = DB::select('SHOW TABLES');
            foreach ($tables as $table) {
                $tableName = current((array) $table);
                if (Schema::hasTable($tableName)) {
                    DB::statement('DROP TABLE '.$tableName);
                }
            }
        } catch (\Exception $e) {
            throw new \Exception('Error dropping tables: '.$e->getMessage());
        }
    }

    protected function importDataFromSqlFile()
    {
        try {
            $sqlFilePath = base_path('database/cargo.sql');
            DB::unprepared(file_get_contents($sqlFilePath));
        } catch (\Exception $e) {
            throw new \Exception('Error importing data from cargo.sql: '.$e->getMessage());
        }
    }

    protected function deletePublicStorage()
    {
        try {
            $storagePath = public_path('storage');
            File::deleteDirectory($storagePath);
        } catch (\Exception $e) {
            throw new \Exception('Error deleting public/storage: '.$e->getMessage());
        }
    }

    protected function linkPublicStorage()
    {
        try {
            $this->call('storage:link');
        } catch (\Exception $e) {
            throw new \Exception('Error running storage:link: '.$e->getMessage());
        }
    }

    protected function displayErrorDetails($e)
    {
        $this->line('');
        $this->error('Error Details:');
        $this->line($e->getMessage());
        $this->line('File: '.$e->getFile());
        $this->line('Line: '.$e->getLine());
        $this->line('Trace: '.$e->getTraceAsString());
        $this->line('');
    }
}