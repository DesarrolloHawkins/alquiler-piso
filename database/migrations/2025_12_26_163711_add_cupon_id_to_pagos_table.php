<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->foreignId('cupon_id')->nullable()->after('cliente_id')->constrained('cupones')->onDelete('set null');
            $table->decimal('descuento_aplicado', 10, 2)->nullable()->after('monto')->comment('Descuento aplicado por el cupón');
            $table->decimal('monto_original', 10, 2)->nullable()->after('descuento_aplicado')->comment('Monto original antes del descuento');
            
            $table->index('cupon_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropForeign(['cupon_id']);
            $table->dropColumn(['cupon_id', 'descuento_aplicado', 'monto_original']);
        });
    }
};
