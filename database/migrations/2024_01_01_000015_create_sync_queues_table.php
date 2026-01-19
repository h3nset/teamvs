<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_queues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tournament_id')->nullable();
            $table->string('device_id');
            $table->string('entity_type');
            $table->uuid('entity_id');
            $table->json('payload');
            $table->enum('status', ['pending', 'processing', 'synced', 'failed', 'conflict'])->default('pending');
            $table->integer('retry_count')->default(0);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->foreign('tournament_id')
                ->references('id')
                ->on('tournaments')
                ->onDelete('cascade');

            $table->index(['status', 'created_at']);
            $table->index('device_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_queues');
    }
};
