<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('ppa_company_name')->nullable(); // Place of Primary Assignment company name
            $table->text('ppa_address')->nullable(); // PPA address
            $table->decimal('ppa_latitude', 10, 8)->nullable(); // PPA location coordinates
            $table->decimal('ppa_longitude', 11, 8)->nullable();
            $table->date('siwes_start_date')->nullable(); // When SIWES started (first activity log)
            $table->date('siwes_end_date')->nullable(); // Calculated end date (start + 24 weeks)
            $table->boolean('siwes_completed')->default(false); // If 24 weeks completed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'ppa_company_name',
                'ppa_address', 
                'ppa_latitude',
                'ppa_longitude',
                'siwes_start_date',
                'siwes_end_date',
                'siwes_completed'
            ]);
        });
    }
};
