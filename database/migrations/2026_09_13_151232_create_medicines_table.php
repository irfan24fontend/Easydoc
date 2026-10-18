<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();

            $table->string('brand_name');       // Napa, Napa Extend
            $table->string('generic_name');     // Paracetamol
            $table->string('strength')->nullable();   // 500 mg
            $table->string('company')->nullable();    // Beximco, Square
            $table->enum('dosage_form', ['Tablet', 'Capsule', 'Syrup', 'Injection', 'Drop', 'Cream', 'Inhaler', 'Other'])->default('Tablet');
            $table->string('category')->nullable();   // Antibiotic, Analgesic
            $table->string('shortcut')->nullable()->unique(); // pcm -> Paracetamol
            $table->unsignedInteger('usage_count')->default(0); // suggestion ranking এর জন্য (system নিজে বাড়াবে)
            $table->boolean('status')->default(true);

            $table->timestamps();

            $table->index(['brand_name', 'generic_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};