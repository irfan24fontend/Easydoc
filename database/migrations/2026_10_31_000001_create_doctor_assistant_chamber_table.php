<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_assistant_chamber', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_assistant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chamber_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['doctor_assistant_id', 'chamber_id']);
        });

        // Keep existing assistants working, but scope them to the doctor's current active chamber.
        $assignments = DB::table('doctor_assistants')
            ->join('chambers', function ($join) {
                $join->on('chambers.doctor_id', '=', 'doctor_assistants.doctor_id')
                    ->where('chambers.status', true)
                    ->where('chambers.doctor_active', true);
            })
            ->select('doctor_assistants.id as doctor_assistant_id', 'chambers.id as chamber_id')
            ->get()
            ->map(fn ($row) => [
                'doctor_assistant_id' => $row->doctor_assistant_id,
                'chamber_id' => $row->chamber_id,
                'created_at' => now(),
                'updated_at' => now(),
            ])->all();

        if ($assignments) {
            DB::table('doctor_assistant_chamber')->insert($assignments);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_assistant_chamber');
    }
};
