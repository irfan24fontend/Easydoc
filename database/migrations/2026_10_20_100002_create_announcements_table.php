<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Messages the Super Admin broadcasts to all doctors (shown on the doctor "News" page)
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->default('news');   // feature | news | notice
            $table->string('title', 160);
            $table->text('body');
            $table->string('link_url')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
