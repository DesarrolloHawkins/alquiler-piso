<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->string('numero_personas')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('reservas', function (Blueprint $table) {
        });
    }
};
