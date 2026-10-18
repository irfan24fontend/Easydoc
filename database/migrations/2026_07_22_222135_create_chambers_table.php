<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chambers', function (Blueprint $table) {

            $table->id();

            $table->foreignId('doctor_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedBigInteger('prescription_template_id')->nullable();

            $table->string('chamber_name');

            $table->string('hospital_name');

            $table->string('phone');

            $table->string('email')->nullable();

            $table->text('address');

            $table->text('visiting_days');

            $table->string('visiting_time');

            $table->boolean('status')
                ->default(false);

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chambers');
    }
};