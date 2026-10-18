<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->boolean('is_sponsored')->default(false)->after('status');
            $table->string('sponsor_company')->nullable()->after('is_sponsored'); // কোন কোম্পানি sponsor করেছে (optional note)
            $table->unsignedInteger('sponsor_priority')->default(0)->after('sponsor_company'); // একাধিক sponsored ব্র্যান্ড একই generic এ থাকলে কোনটা আগে
        });
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn(['is_sponsored', 'sponsor_company', 'sponsor_priority']);
        });
    }
};