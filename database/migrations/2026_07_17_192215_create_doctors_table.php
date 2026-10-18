<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('bmdc_number')->nullable()->unique();

            $table->string('doctor_code')->unique();

            $table->string('speciality')->nullable();

            $table->string('qualification')->nullable();

            $table->string('designation')->nullable();

            $table->string('experience')->nullable();

            $table->string('gender')->nullable();

            $table->date('date_of_birth')->nullable();

            $table->string('photo')->nullable();

            $table->longText('about')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};