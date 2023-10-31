<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('apartamento_limpieza', function (Blueprint $table) {
            $table->tinyInteger('dormitorio_photo')->nullable();
            $table->tinyInteger('bano_photo')->nullable();
            $table->tinyInteger('armario_photo')->nullable();
            $table->tinyInteger('canape_photo')->nullable();
            $table->tinyInteger('salon_photo')->nullable();
            $table->tinyInteger('cocina_photo')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('apartamento_limpieza', function (Blueprint $table) {
        });
    }
};
