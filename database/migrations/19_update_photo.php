<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->unsignedBigInteger('reserva_id')->nullable();
            $table->unsignedBigInteger('cliente_id')->nullable();

            $table->foreign('reserva_id')->references('id')->on('reservas');
            $table->foreign('cliente_id')->references('id')->on('clientes');

        });
    }
    
    public function down()
    {
        Schema::table('photos', function (Blueprint $table) {
        });
    }
};
