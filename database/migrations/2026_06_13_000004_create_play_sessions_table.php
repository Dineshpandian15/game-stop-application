<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('play_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pricing_package_id')->nullable()->constrained()->nullOnDelete();
            $table->string('game_type');
            $table->unsignedTinyInteger('player_count');
            $table->unsignedSmallInteger('planned_duration_minutes');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->unsignedInteger('total_paused_seconds')->default(0);
            $table->string('status')->default('active');
            $table->decimal('amount', 10, 2)->default(0);
            $table->boolean('is_birthday_offer')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['station_id', 'status']);
            $table->index(['status', 'ended_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('play_sessions');
    }
};
