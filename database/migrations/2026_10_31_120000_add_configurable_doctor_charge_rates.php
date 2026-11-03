<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->decimal('investigation_advice_charge', 8, 2)->nullable();
            $table->decimal('ai_diagnosis_charge', 8, 2)->nullable();
            $table->decimal('ai_medicine_charge', 8, 2)->nullable();
            $table->decimal('ai_interaction_charge', 8, 2)->nullable();
            $table->decimal('chamber_request_charge', 8, 2)->nullable();
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->string('idempotency_key', 120)->nullable();
            $table->unique(['wallet_id', 'idempotency_key'], 'wallet_transaction_idempotency_unique');
        });

        Schema::table('chambers', function (Blueprint $table) {
            $table->string('request_token', 120)->nullable();
            $table->unique(['doctor_id', 'request_token'], 'chamber_request_token_unique');
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropUnique('wallet_transaction_idempotency_unique');
            $table->dropColumn('idempotency_key');
        });

        Schema::table('chambers', function (Blueprint $table) {
            $table->dropUnique('chamber_request_token_unique');
            $table->dropColumn('request_token');
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn([
                'investigation_advice_charge',
                'ai_diagnosis_charge',
                'ai_medicine_charge',
                'ai_interaction_charge',
                'chamber_request_charge',
            ]);
        });
    }
};
