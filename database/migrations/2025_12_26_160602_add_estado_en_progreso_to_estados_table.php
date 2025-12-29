<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verificar si el estado "En progreso" ya existe
        $estadoExiste = DB::table('estados')->where('id', 5)->exists();
        
        if (!$estadoExiste) {
            DB::table('estados')->insert([
                'id' => 5,
                'nombre' => 'En progreso',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar el estado "En progreso" si existe
        DB::table('estados')->where('id', 5)->delete();
    }
};
