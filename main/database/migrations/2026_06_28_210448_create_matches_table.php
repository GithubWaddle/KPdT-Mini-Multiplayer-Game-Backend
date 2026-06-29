<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player1_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('player2_id')->constrained('users')->cascadeOnDelete();
            // winner_id is nullable — only set when match finishes
            $table->foreignId('winner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['waiting', 'ongoing', 'finished'])->default('waiting');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};