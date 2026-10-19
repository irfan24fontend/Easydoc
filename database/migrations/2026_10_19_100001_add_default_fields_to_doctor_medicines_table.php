<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_medicines', function (Blueprint $table) {
            // "Sticky" medicine: added automatically to every new prescription with this dose
            $table->boolean('is_default')->default(false);
            $table->string('default_dose', 40)->nullable();        // e.g. 1+0+1
            $table->string('default_dose_text', 120)->nullable();  // syrup etc., e.g. "২ চামচ"
            $table->string('default_duration', 120)->nullable();
            $table->string('default_instruction', 255)->nullable();
            $table->unsignedInteger('default_sort')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('doctor_medicines', function (Blueprint $table) {
            $table->dropColumn([
                'is_default',
                'default_dose',
                'default_dose_text',
                'default_duration',
                'default_instruction',
                'default_sort',
            ]);
        });
    }
};
