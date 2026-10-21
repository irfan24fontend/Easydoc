<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recharge_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('wallet_id')->index();

            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 30)->default('bkash');
            $table->string('sender_number', 20);          // bKash number the money was sent from
            $table->string('trx_id', 40)->index();        // bKash TrxID
            $table->text('doctor_note')->nullable();

            $table->string('status', 20)->default('pending')->index(); // pending | approved | rejected
            $table->text('admin_note')->nullable();       // rejection reason
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->unsignedBigInteger('wallet_invoice_id')->nullable()->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recharge_requests');
    }
};
