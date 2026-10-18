<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();

            $table->foreignId('doctor_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('mobile');
            $table->unsignedTinyInteger('age')->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->string('blood_group')->nullable();
            $table->text('address')->nullable();

            $table->timestamps();

            $table->unique(['doctor_id', 'mobile']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};