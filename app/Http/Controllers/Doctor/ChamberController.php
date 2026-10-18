<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Chamber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChamberController extends Controller
{
    /**
     * Doctor-er nijer chamber-er list + ekhon kon chamber "chalu" ($working).
     * "Chalu" = Super Admin Active (status) AND doctor nije chalu rekheche (doctor_active).
     */
    public function index(Request $request)
    {
        $doctor = $request->user()->doctor;
        abort_unless($doctor, 403);

        $chambers = $doctor->chambers()->with('template')->get();

        $working = $chambers->first(function ($c) {
            return (bool) $c->status && (bool) $c->doctor_active;
        });

        return view('doctor.chambers.index', compact('chambers', 'working'));
    }

    /**
     * Doctor-er nijer switch: ekta chamber chalu/bondho.
     * - Super Admin Inactive thakle chalu kora jabe na.
     * - Ekshathe shudhu ekta chamber chalu thake.
     */
    public function toggle(Request $request, int $chamber)
    {
        $doctor = $request->user()->doctor;
        abort_unless($doctor, 403);

        $chamber = Chamber::where('doctor_id', $doctor->id)->findOrFail($chamber);

        if (! $chamber->status) {
            return back()->with('error', 'Super Admin এই চেম্বার Inactive করে রেখেছেন, তাই চালু করা যাবে না।');
        }

        $turnedOn = false;

        DB::transaction(function () use ($doctor, $chamber, &$turnedOn) {
            if ($chamber->doctor_active) {
                $chamber->forceFill(['doctor_active' => false])->save();
            } else {
                Chamber::where('doctor_id', $doctor->id)
                    ->where('id', '!=', $chamber->id)
                    ->update(['doctor_active' => false]);

                $chamber->forceFill(['doctor_active' => true])->save();
                $turnedOn = true;
            }
        });

        return back()->with(
            'success',
            $turnedOn
                ? '"' . $chamber->chamber_name . '" এখন চালু আছে।'
                : '"' . $chamber->chamber_name . '" বন্ধ করা হয়েছে।'
        );
    }
}