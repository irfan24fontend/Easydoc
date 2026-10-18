<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_profiles', function (Blueprint $table) {
            $table->id();

            // One doctor = one public profile
            $table->foreignId('doctor_id')->unique()->constrained()->cascadeOnDelete();

            $table->string('slug', 80)->unique();
            $table->string('tagline', 160);
            $table->text('about');

            $table->json('education');                 // [{degree, institution, year}]
            $table->json('work_experience')->nullable(); // [{position, organization, from, to}]
            $table->json('expertise');                 // ["Diabetes", "Thyroid", ...]
            $table->json('languages')->nullable();     // ["Bangla", "English"]

            $table->unsignedInteger('consultation_fee')->nullable();
            $table->string('website_url')->nullable();
            $table->string('facebook_url')->nullable();

            // Phone/email are private unless the doctor switches this on
            $table->boolean('show_contact')->default(false);
            $table->boolean('is_published')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_profiles');
    }
};
