<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tournament_id');
            $table->uuid('home_pair_id');
            $table->uuid('away_pair_id');
            $table->integer('round_number');
            $table->integer('match_number');
            $table->integer('court_number')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->foreign('tournament_id')
                ->references('id')
                ->on('tournaments')
                ->onDelete('cascade');

            $table->foreign('home_pair_id')
                ->references('id')
                ->on('pairs')
                ->onDelete('cascade');

            $table->foreign('away_pair_id')
                ->references('id')
                ->on('pairs')
                ->onDelete('cascade');

            $table->index(['tournament_id', 'round_number']);
            $table->index('status');
            $table->unique(['home_pair_id', 'away_pair_id'], 'unique_match_pairing');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
