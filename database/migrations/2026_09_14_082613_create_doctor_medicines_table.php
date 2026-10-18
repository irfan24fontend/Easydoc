<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_medicines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();

            $table->boolean('is_favorite')->default(false);
            $table->unsignedInteger('usage_count')->default(0);

            $table->timestamps();

            $table->unique(['doctor_id', 'medicine_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_medicines');
    }
};