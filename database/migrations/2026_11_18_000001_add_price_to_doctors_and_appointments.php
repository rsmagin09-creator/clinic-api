<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Стоимость приёма врача и цена, зафиксированная в записи на приём (в тенге). */
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->unsignedInteger('price')->default(5000)->after('experience_years');
        });
        Schema::table('appointments', function (Blueprint $table) {
            $table->unsignedInteger('price')->nullable()->after('reason');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', fn (Blueprint $table) => $table->dropColumn('price'));
        Schema::table('doctors', fn (Blueprint $table) => $table->dropColumn('price'));
    }
};
