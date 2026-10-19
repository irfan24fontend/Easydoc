<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_favorite_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();

            // Exactly as stored in medicines.company (chosen from the existing list)
            $table->string('company');
            $table->unsignedInteger('priority')->default(0);

            $table->timestamps();

            $table->unique(['doctor_id', 'company']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_favorite_companies');
    }
};
