<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('serials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chamber_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('prescription_id')->nullable()->constrained()->nullOnDelete();

            $table->date('serial_date');
            $table->unsignedInteger('serial_number');
            $table->string('status', 20)->default('waiting'); // waiting | in_progress | done | skipped | cancelled

            $table->string('reason')->nullable();
            $table->string('bp', 20)->nullable();
            $table->string('pulse', 20)->nullable();
            $table->string('temperature', 20)->nullable();
            $table->timestamp('called_at')->nullable();

            $table->timestamps();

            $table->unique(['doctor_id', 'serial_date', 'serial_number']);
            $table->index(['doctor_id', 'serial_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('serials');
    }
};