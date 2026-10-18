<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->string('medical_college')->nullable();
            $table->string('bmdc_certificate')->nullable();

            // Existing doctors (created by Super Admin) stay approved: default = approved.
            // Self-registered doctors are saved as "pending" by DoctorRegistrationService.
            $table->string('approval_status', 20)->default('approved')->index();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->text('rejection_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropIndex(['approval_status']);
            $table->dropColumn([
                'medical_college',
                'bmdc_certificate',
                'approval_status',
                'approved_at',
                'approved_by',
                'rejection_reason',
            ]);
        });
    }
};
