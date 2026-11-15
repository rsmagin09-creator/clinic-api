<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete(); // пациент
            $table->foreignId('doctor_id')->constrained()->restrictOnDelete();
            $table->date('date');
            $table->time('time');
            $table->string('status', 20)->default('scheduled')->index();
            $table->string('reason')->nullable();
            $table->timestamps();
            $table->index(['doctor_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
