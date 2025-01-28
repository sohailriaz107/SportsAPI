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
        Schema::create('a_p_i_s', function (Blueprint $table) {
            $table->id();
            $table->string('match'); // Match details
            $table->string('team'); // Team name
            $table->string('league'); // League name
            $table->integer('page_number'); // Page number to store
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('a_p_i_s');
    }
};
