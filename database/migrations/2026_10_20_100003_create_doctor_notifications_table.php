<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per doctor per notification (broadcast copies + personal ones like "recharge approved")
        Schema::create('doctor_notifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('announcement_id')->nullable()->constrained()->cascadeOnDelete();

            $table->string('type', 20)->default('notice'); // feature | news | notice | recharge
            $table->string('title', 160);
            $table->text('body');
            $table->string('link_url')->nullable();

            $table->timestamp('read_at')->nullable();
            $table->timestamp('popup_at')->nullable();     // set when the pop-up was delivered to the browser

            $table->timestamps();

            $table->index(['doctor_id', 'read_at']);
            $table->index(['doctor_id', 'popup_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_notifications');
    }
};
