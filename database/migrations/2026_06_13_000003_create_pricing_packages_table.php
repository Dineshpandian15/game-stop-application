<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_packages', function (Blueprint $table) {
            $table->id();
            $table->string('game_type');
            $table->unsignedTinyInteger('player_count');
            $table->unsignedSmallInteger('duration_minutes');
            $table->decimal('price', 10, 2);
            $table->string('label')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['game_type', 'player_count', 'duration_minutes']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_packages');
    }
};
