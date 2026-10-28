<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * offline_ref: অফলাইনে তৈরি প্রেসক্রিপশনের client-side UUID।
     * sync-এর সময় একই offline_ref দ্বিতীয়বার এলে নতুন প্রেসক্রিপশন/চার্জ তৈরি হবে না (idempotent),
     * যাতে নেট flaky হয়ে sync দুইবার চললেও ডাক্তারের ব্যালেন্স দুইবার না কাটে।
     */
    public function up(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->string('offline_ref', 64)->nullable()->after('parent_prescription_id');
            $table->unique(['doctor_id', 'offline_ref'], 'prescriptions_doctor_offline_ref_unique');
        });
    }

    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropUnique('prescriptions_doctor_offline_ref_unique');
            $table->dropColumn('offline_ref');
        });
    }
};
