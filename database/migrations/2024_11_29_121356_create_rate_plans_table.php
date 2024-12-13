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
        Schema::create('rate_plans', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('property_id')->constrained('apartamentos')->onDelete('cascade');
            $table->foreignId('room_type_id')->nullable()->constrained('room_types')->onDelete('cascade');
            $table->string('tax_set_id')->nullable();
            $table->string('parent_rate_plan_id')->nullable();
            $table->decimal('children_fee', 10, 2)->default(0);
            $table->decimal('infant_fee', 10, 2)->default(0);
            $table->json('max_stay')->nullable();
            $table->json('min_stay_arrival')->nullable();
            $table->json('min_stay_through')->nullable();
            $table->json('closed_to_arrival')->nullable();
            $table->json('closed_to_departure')->nullable();
            $table->json('stop_sell')->nullable();
            $table->json('options')->nullable();
            $table->string('currency', 3);
            $table->string('sell_mode');
            $table->string('rate_mode');
            $table->string('id_channex')->nullable(); // ID devuelto por Channex
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rate_plans');
    }
};
