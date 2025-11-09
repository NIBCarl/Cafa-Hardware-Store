<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixOrphanedTablespace extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:fix-tablespace {table=settings}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix orphaned tablespace files (MySQL Error 1813)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tableName = $this->argument('table');
        
        $this->info("Attempting to fix orphaned tablespace for table: {$tableName}");
        
        try {
            // Get database name
            $database = DB::connection()->getDatabaseName();
            $this->info("Database: {$database}");
            
            // Get data directory
            $result = DB::select("SHOW VARIABLES LIKE 'datadir'");
            $dataDir = $result[0]->Value ?? null;
            
            if ($dataDir) {
                $this->info("MySQL Data Directory: {$dataDir}");
                
                // MySQL encodes special characters in directory names (e.g., - becomes @002d)
                $encodedDatabase = $this->encodeDatabaseName($database);
                $idbFilePath = $dataDir . $encodedDatabase . DIRECTORY_SEPARATOR . $tableName . '.ibd';
                $this->warn("Orphaned file location: {$idbFilePath}");
            }
            
            // Method 1: Try to drop with foreign key checks disabled
            $this->info("Method 1: Dropping table with foreign key checks disabled...");
            DB::statement("SET FOREIGN_KEY_CHECKS=0");
            DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
            DB::statement("SET FOREIGN_KEY_CHECKS=1");
            $this->info("✓ Method 1 completed");
            
            // Method 2: Try to create and immediately drop the table to clean up
            $this->info("Method 2: Attempting to recreate and drop table...");
            try {
                DB::statement("CREATE TABLE IF NOT EXISTS `{$tableName}` (id INT) ENGINE=InnoDB");
                DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
                $this->info("✓ Method 2 completed");
            } catch (\Exception $e) {
                $this->warn("Method 2 failed: " . $e->getMessage());
            }
            
            // Check if file still exists
            $this->info("\nChecking if tablespace issue is resolved...");
            
            // Try to check information schema
            $tableExists = DB::select("
                SELECT COUNT(*) as count 
                FROM information_schema.TABLES 
                WHERE TABLE_SCHEMA = ? 
                AND TABLE_NAME = ?
            ", [$database, $tableName]);
            
            if ($tableExists[0]->count == 0) {
                $this->info("✓ Table removed from information schema");
            } else {
                $this->warn("⚠ Table still exists in information schema");
            }
            
            $this->newLine();
            $this->info("Tablespace cleanup completed!");
            $this->newLine();
            
            if ($dataDir) {
                $encodedDatabase = $this->encodeDatabaseName($database);
                $this->warn("MANUAL ACTION REQUIRED:");
                $this->warn("If the issue persists, manually delete these files:");
                $this->line("  - {$dataDir}{$encodedDatabase}\\{$tableName}.ibd");
                $this->line("  - {$dataDir}{$encodedDatabase}\\{$tableName}.frm (if exists)");
                $this->newLine();
                $this->info("After manual deletion, run: php artisan migrate");
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Encode database name for filesystem (MySQL encodes special chars)
     * Example: cafe-pos becomes cafe@002dpos
     */
    private function encodeDatabaseName(string $dbName): string
    {
        $encoded = '';
        for ($i = 0; $i < strlen($dbName); $i++) {
            $char = $dbName[$i];
            // MySQL encodes special characters as @XXXX where XXXX is hex
            if (!ctype_alnum($char) && $char !== '_') {
                $encoded .= '@' . str_pad(dechex(ord($char)), 4, '0', STR_PAD_LEFT);
            } else {
                $encoded .= $char;
            }
        }
        return $encoded;
    }
}
