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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('email')->nullable()->after('name'); // Add email column
            $table->string('phone')->nullable()->after('email'); // Add phone column
            $table->text('bio')->nullable()->after('phone');     // Add bio column
            $table->string('logo')->nullable()->after('bio');    // Add logo column
            $table->integer('sportma_id')->nullable()->after('logo'); // Add sportma_id column (assuming it's an integer)
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['email', 'phone', 'bio', 'logo', 'sportma_id']); // Drop columns if rolled back
        });
    }
    

};