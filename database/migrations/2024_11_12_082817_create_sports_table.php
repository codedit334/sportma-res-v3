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
    Schema::create('sports', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('company_id');  // Foreign key to company
        $table->string('type');  // e.g., Football, Padel
        $table->timestamps();

        // Foreign Key Constraints
        $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sports');
    }
};