<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('prescriptions', 'share_token')) {
            return;
        }
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->string('share_token', 64)->nullable()->unique();
            $table->timestamp('shared_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropUnique(['share_token']);
            $table->dropColumn(['share_token', 'shared_at']);
        });
    }
};
