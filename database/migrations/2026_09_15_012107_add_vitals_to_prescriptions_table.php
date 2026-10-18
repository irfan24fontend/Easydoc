<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->string('bp')->nullable()->after('chamber_id');
            $table->string('pulse')->nullable()->after('bp');
            $table->string('temperature')->nullable()->after('pulse');
        });
    }

    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropColumn(['bp', 'pulse', 'temperature']);
        });
    }
};