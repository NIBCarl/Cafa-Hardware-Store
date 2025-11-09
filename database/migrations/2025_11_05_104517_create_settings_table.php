<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the table if it exists in the schema
        Schema::dropIfExists('settings');
        
        // Handle orphaned tablespace (MySQL-specific fix for error 1813)
        try {
            // Check if we're using MySQL
            if (DB::connection()->getDriverName() === 'mysql') {
                // Try to discard the tablespace if it exists
                DB::statement("SET FOREIGN_KEY_CHECKS=0");
                DB::statement("DROP TABLE IF EXISTS `settings`");
                DB::statement("SET FOREIGN_KEY_CHECKS=1");
            }
        } catch (\Exception $e) {
            // If the table doesn't exist in schema, manually remove orphaned .ibd file reference
            try {
                $database = DB::connection()->getDatabaseName();
                // Force remove any orphaned tablespace references
                DB::statement("DROP TABLE IF EXISTS `{$database}`.`settings`");
            } catch (\Exception $innerException) {
                // Silently continue if no orphaned tablespace exists
            }
        }
        
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
