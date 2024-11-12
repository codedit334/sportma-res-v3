<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('terrains', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sport_id'); // Foreign key to sports table
            $table->string('label'); // E.g., "P1", "F1", etc.
            $table->uuid('terrainID')->unique();
            $table->json('prices')->nullable(); // Optional prices JSON field
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('sport_id')->references('id')->on('sports')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('terrains');
    }
};