<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->string('type')->default('full')->after('chamber_id'); // full | investigation_only
            $table->foreignId('parent_prescription_id')->nullable()->after('type')
                ->constrained('prescriptions')->nullOnDelete();
            $table->text('investigation_findings')->nullable()->after('investigations');
        });
    }

    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropForeign(['parent_prescription_id']);
            $table->dropColumn(['type', 'parent_prescription_id', 'investigation_findings']);
        });
    }
};