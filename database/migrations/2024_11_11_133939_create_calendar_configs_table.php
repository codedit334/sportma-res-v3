<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCalendarConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calendar_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id'); // Ensure only one config per company
            $table->unsignedBigInteger('created_by_user_id');
            $table->json('configurations');  // Store configurations as JSON
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('calendar_configs');
    }
}