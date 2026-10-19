<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chambers', function (Blueprint $table) {
            // Purono shob chamber 'approved' thakbe, tai ager kaj bhangbe na.
            // Notun request 'waiting_request' niye toiri hobe.
            $table->string('request_status', 20)->default('approved')->after('status');
            $table->string('pad_image')->nullable()->after('visiting_time');
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->string('reject_reason')->nullable();
            $table->decimal('fee_charged', 10, 2)->default(0);

            $table->index('request_status');
        });
    }

    public function down(): void
    {
        Schema::table('chambers', function (Blueprint $table) {
            $table->dropIndex(['request_status']);
            $table->dropColumn([
                'request_status', 'pad_image', 'requested_at',
                'reviewed_at', 'reviewed_by', 'reject_reason', 'fee_charged',
            ]);
        });
    }
};
