<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('doctor')->change();
        });
    }

    public function down(): void
    {
        // column প্রসারিত করা হয়েছে, ফেরত নেওয়ার দরকার নেই
    }
};