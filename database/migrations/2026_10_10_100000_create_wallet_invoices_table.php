<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->nullable()->unique();
            $table->unsignedBigInteger('wallet_id')->index();
            $table->unsignedBigInteger('doctor_id')->index();
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_before', 12, 2)->default(0);
            $table->decimal('balance_after', 12, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->string('reference')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('issued_by')->nullable();
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_invoices');
    }
};