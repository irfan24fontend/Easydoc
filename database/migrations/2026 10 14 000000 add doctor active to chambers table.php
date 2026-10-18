<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('chambers', 'doctor_active')) {
            Schema::table('chambers', function (Blueprint $table) {
                // Doctor nije ei chamber "chalu" rekheche kina (Super Admin-er `status` alada)
                $table->boolean('doctor_active')->default(false)->after('status');
            });
        }

        // Ager kaj jeno na thame: proti doctor-er Super-Admin-Active chamber-gulor moddhe
        // prothom ta (sobcheye chhoto id) auto "chalu" kore dei. Baki-gulo doctor nije chalu korbe.
        $ids = DB::table('chambers')
            ->where('status', true)
            ->select('doctor_id', DB::raw('MIN(id) as id'))
            ->groupBy('doctor_id')
            ->pluck('id');

        if ($ids->isNotEmpty()) {
            DB::table('chambers')->whereIn('id', $ids)->update(['doctor_active' => true]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('chambers', 'doctor_active')) {
            Schema::table('chambers', function (Blueprint $table) {
                $table->dropColumn('doctor_active');
            });
        }
    }
};