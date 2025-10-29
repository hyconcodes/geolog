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
        Schema::create('siwes_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('activity_date');
            $table->integer('week_number'); // 1-24 weeks
            $table->enum('day_type', ['weekday', 'saturday']); // weekday (Mon-Fri) or saturday (weekly summary)
            $table->text('activity_description');
            $table->string('document_path')->nullable(); // Optional file upload
            $table->decimal('latitude', 10, 8)->nullable(); // Current location when logging
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_backdated')->default(false); // If logged for past date
            $table->text('backdate_reason')->nullable(); // Reason for backdating
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('approved'); // For backdated entries
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null'); // Supervisor who approved
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['user_id', 'activity_date']);
            $table->index(['user_id', 'week_number']);
            $table->index(['approval_status']);
            
            // Unique constraint to prevent duplicate entries for same day
            $table->unique(['user_id', 'activity_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siwes_activity_logs');
    }
};
