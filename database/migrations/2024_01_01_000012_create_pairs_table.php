<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pairs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('team_id');
            $table->string('player1_name');
            $table->string('player2_name');
            $table->string('display_name')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('team_id')
                ->references('id')
                ->on('teams')
                ->onDelete('cascade');

            $table->index('team_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pairs');
    }
};
