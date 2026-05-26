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
        Schema::create('a_i_queue_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_id')->constrained('a_i_queues')->onDelete('cascade');
            $table->integer('attempt')->default(1);
            $table->string('confidence')->nullable();
            $table->float('score')->nullable();
            $table->text('ai_response')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('a_i_queue_logs');
    }
};
