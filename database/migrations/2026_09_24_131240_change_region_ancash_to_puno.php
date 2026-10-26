<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Huata está en Puno, no en Áncash: corrige la región por defecto y los datos guardados.
     */
    public function up(): void
    {
        Schema::table('producers', function (Blueprint $table) {
            $table->string('region')->default('Puno')->change();
        });

        DB::table('producers')->whereIn('region', ['Ancash', 'Áncash'])->update(['region' => 'Puno']);

        foreach (['Ancash', 'Áncash'] as $wrong) {
            DB::table('users')->where('address', 'like', "%{$wrong}%")
                ->update(['address' => DB::raw("REPLACE(address, '{$wrong}', 'Puno')")]);
            DB::table('sales')->where('client_address', 'like', "%{$wrong}%")
                ->update(['client_address' => DB::raw("REPLACE(client_address, '{$wrong}', 'Puno')")]);
            DB::table('plant_configs')->where('key', 'direccion_planta')->where('value', 'like', "%{$wrong}%")
                ->update(['value' => DB::raw("REPLACE(value, '{$wrong}', 'Puno')")]);
        }

        DB::table('products')->where('specifications', 'like', '%ncash%')->get(['id', 'specifications'])
            ->each(fn ($product) => DB::table('products')->where('id', $product->id)->update([
                'specifications' => str_replace(['Áncash', 'Ancash', 'Áncash'], 'Puno', $product->specifications),
            ]));
    }

    public function down(): void
    {
        Schema::table('producers', function (Blueprint $table) {
            $table->string('region')->default('Ancash')->change();
        });
    }
};
