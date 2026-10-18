<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Patient number column + normal (non-unique) mobile index
        //    (mobile index age add kora hocche, jate doctor_id-r foreign key index-er dorkar mete)
        Schema::table('patients', function (Blueprint $table) {
            $table->unsignedInteger('patient_no')->nullable()->after('doctor_id');
            $table->index(['doctor_id', 'mobile'], 'patients_doctor_mobile_index');
        });

        // 2) Purono patient-der number dewa: proti doctor-er jonno 1, 2, 3 ...
        $counters = [];
        DB::table('patients')
            ->select('id', 'doctor_id')
            ->orderBy('doctor_id')
            ->orderBy('id')
            ->get()
            ->each(function ($p) use (&$counters) {
                $counters[$p->doctor_id] = ($counters[$p->doctor_id] ?? 0) + 1;

                DB::table('patients')
                    ->where('id', $p->id)
                    ->update(['patient_no' => $counters[$p->doctor_id]]);
            });

        // 3) Ekoi mobile-e ekadhik patient-er onumoti din, kintu Patient No unique
        Schema::table('patients', function (Blueprint $table) {
            $table->dropUnique(['doctor_id', 'mobile']);
            $table->unique(['doctor_id', 'patient_no']);
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropUnique(['doctor_id', 'patient_no']);
            $table->dropIndex('patients_doctor_mobile_index');
            $table->dropColumn('patient_no');
            $table->unique(['doctor_id', 'mobile']);
        });
    }
};