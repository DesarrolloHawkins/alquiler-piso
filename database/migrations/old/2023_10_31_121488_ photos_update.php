<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->unsignedBigInteger('cliente_id')->nullable();
            
            $table->foreign('cliente_id')->references('id')->on('clientes');

        });
    }
    
    public function down()
    {
        Schema::table('reservas', function (Blueprint $table) {
        });
    }
};
