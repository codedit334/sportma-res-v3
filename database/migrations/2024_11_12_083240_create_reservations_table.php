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
    Schema::create('reservations', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');  // Foreign key to user
        $table->unsignedBigInteger('terrain_id');  // Foreign key to terrain
        $table->string('title');
        $table->string('class');
        $table->string('split');
        $table->boolean('clickable');
        $table->integer('duration');
        $table->boolean('editable');
        $table->integer('price');
        $table->string('category');
        $table->string('terrain');
        $table->datetime('start');
        $table->datetime('end');
        $table->text('content')->nullable();
        $table->string('status');  // e.g., 'confirmed', 'pending'
        $table->timestamps();

        // Foreign Key Constraints
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('terrain_id')->references('id')->on('terrains')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reservations');
    }
};