<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Прейскурант платных медицинских услуг (цены в тенге). */
    public function up(): void
    {
        Schema::create('medical_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specialty_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->unsignedInteger('price');
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['specialty_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_services');
    }
};
