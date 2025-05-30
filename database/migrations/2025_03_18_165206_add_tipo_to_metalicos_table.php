<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('metalicos', function (Blueprint $table) {
            $table->enum('tipo', ['ingreso', 'gasto'])->default('ingreso');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('metalicos', function (Blueprint $table) {
            //
        });
    }
};
