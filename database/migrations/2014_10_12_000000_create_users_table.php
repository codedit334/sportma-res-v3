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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');  // Foreign key to company
            $table->string('name');
            $table->string('email')->unique();
            $table->string('role');
            $table->json('permissions')->nullable();
            $table->string('profile_picture')->nullable();
            $table->string('password');
            $table->boolean('is_admin')->default(false);
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
        Schema::dropIfExists('users');
    }
};