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
        Schema::table('terrains', function (Blueprint $table) {
            $table->string('address')->nullable(); // Add nullable address column
            $table->boolean('sportma')->default(false); // Add sportma column with a default value of false
            $table->integer('sportma_terrain_id')->nullable(); // Add nullable sportma_terrain_id column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('terrains', function (Blueprint $table) {
            $table->dropColumn(['address', 'sportma', 'sportma_terrain_id']);
        });
    }
};