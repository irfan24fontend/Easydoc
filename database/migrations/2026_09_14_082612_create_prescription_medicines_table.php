<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescription_medicines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->nullable()->constrained()->nullOnDelete();

            // snapshot - medicine পরে edit/delete হলেও পুরনো প্রেসক্রিপশন অক্ষত থাকবে
            $table->string('medicine_name');

            $table->decimal('dose_morning', 3, 1)->default(0);
            $table->decimal('dose_noon', 3, 1)->default(0);
            $table->decimal('dose_night', 3, 1)->default(0);

            $table->string('duration')->nullable();
            $table->string('instruction')->nullable();
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_medicines');
    }
};