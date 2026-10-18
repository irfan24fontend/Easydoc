<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescription_medicines', function (Blueprint $table) {
            $table->string('medicine_type')->nullable()->after('medicine_name'); // Tablet, Capsule, Syrup, Injection...
            $table->string('dose_text')->nullable()->after('dose_night'); // syrup/free-text dose e.g. "২ চামচ"
        });
    }

    public function down(): void
    {
        Schema::table('prescription_medicines', function (Blueprint $table) {
            $table->dropColumn(['medicine_type', 'dose_text']);
        });
    }
};