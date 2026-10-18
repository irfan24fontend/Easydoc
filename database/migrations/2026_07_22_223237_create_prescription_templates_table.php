<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescription_templates', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->string('slug')->unique();

            $table->enum('paper_size',[
                'A4',
                'A5'
            ])->default('A4');

            $table->longText('html');

            $table->longText('css')->nullable();

            $table->boolean('status')->default(true);

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_templates');
    }
};