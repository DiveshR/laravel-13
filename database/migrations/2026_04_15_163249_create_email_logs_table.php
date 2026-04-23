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
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            // Relations
            $table->foreignId('campaign_id')
                ->constrained('email_campaigns')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            // Email tracking
            $table->string('email');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');

            // Error handling
            $table->text('error')->nullable();

            // Tracking (future use)
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamps();

            // Indexes (VERY IMPORTANT for 10k+ records)
            $table->index(['campaign_id', 'status']);
            $table->index(['user_id']);
            $table->index(['email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
