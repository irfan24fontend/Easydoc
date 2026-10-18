<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chamber_id')->nullable()->constrained()->nullOnDelete();

            $table->text('clinical_complaints')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('risk_factors')->nullable();
            $table->text('investigations')->nullable();
            $table->text('advice')->nullable();

            $table->date('followup_date')->nullable();
            $table->boolean('followup_reminder_sent')->default(false);

            $table->decimal('charge_deducted', 8, 2)->default(0);

            $table->timestamps();

            $table->index('followup_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};