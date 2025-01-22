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
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->onDelete('cascade');
            $table->foreignId('team_1_id')->constrained('teams')->onDelete('cascade');
            $table->foreignId('team_2_id')->constrained('teams')->onDelete('cascade');
            $table->integer('team_1_score')->default(0);
            $table->integer('team_2_score')->default(0);
            $table->enum('status', ['upcoming', 'live', 'finished'])->default('upcoming');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};
