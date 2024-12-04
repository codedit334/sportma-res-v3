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
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->boolean('sportma')->nullable()->after('status'); 
            $table->uuid('sportma_reservation_id')->nullable()->after('sportma');
            
            // Adding new boolean columns with default values
            $table->boolean('titleEditable')->default(true)->after('sportma_reservation_id');
            $table->boolean('deletable')->default(false)->after('titleEditable');
            $table->boolean('draggable')->default(true)->after('deletable');
            $table->boolean('resizable')->default(true)->after('draggable');
        });
    }
    
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'sportma',
                'sportma_reservation_id',
                'titleEditable',
                'deletable',
                'draggable',
                'resizable'
            ]);
        });
    }
};