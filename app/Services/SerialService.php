<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Prescription;
use App\Models\Serial;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class SerialService
{
    /** Koto din ager porjonto advance serial deya jabe (ajker thekey) */
    public const MAX_ADVANCE_DAYS = 30;

    public function __construct(protected PatientService $patients) {}

    protected static function tz(): string
    {
        return config('easydoc.timezone', 'Asia/Dhaka');
    }

    /** ঢাকার সময় অনুযায়ী আজকের তারিখ (Y-m-d) */
    public static function today(): string
    {
        return Carbon::now(self::tz())->toDateString();
    }

    /** সর্বোচ্চ কত তারিখ পর্যন্ত advance সিরিয়াল দেওয়া যাবে */
    public static function maxDate(): string
    {
        return Carbon::now(self::tz())->addDays(self::MAX_ADVANCE_DAYS)->toDateString();
    }

    /** ?date=... বৈধ Y-m-d না হলে আজকের তারিখ ধরে */
    public function normalizeDate(?string $date): string
    {
        if ($date && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $m) && checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            return $date;
        }

        return self::today();
    }

    /** নির্দিষ্ট তারিখের কিউ */
    public function queueFor(Doctor $doctor, string $date)
    {
        return Serial::with('patient')
            ->where('doctor_id', $doctor->id)
            ->where('serial_date', $date)
            ->orderBy('serial_number')
            ->get();
    }

    public function todaysQueue(Doctor $doctor)
    {
        return $this->queueFor($doctor, self::today());
    }

    /** আজ ও সামনের যেসব তারিখে সিরিয়াল আছে (তারিখ + সংখ্যা) */
    public function upcomingDates(Doctor $doctor, int $limit = 7)
    {
        return Serial::where('doctor_id', $doctor->id)
            ->where('serial_date', '>=', self::today())
            ->where('status', '!=', 'cancelled')
            ->selectRaw('serial_date, COUNT(*) as total')
            ->groupBy('serial_date')
            ->orderBy('serial_date')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'date' => Carbon::parse($r->serial_date)->toDateString(),
                'count' => (int) $r->total,
            ]);
    }

    public function counts($serials): array
    {
        return [
            'total' => $serials->where('status', '!=', 'cancelled')->count(),
            'waiting' => $serials->where('status', 'waiting')->count(),
            'in_progress' => $serials->where('status', 'in_progress')->count(),
            'done' => $serials->where('status', 'done')->count(),
            'skipped' => $serials->where('status', 'skipped')->count(),
        ];
    }

    /**
     * নতুন সিরিয়াল তৈরি (আজ বা সামনের তারিখের জন্য)।
     * নাম্বার প্রতিটি তারিখে আলাদাভাবে ১ থেকে শুরু হয়।
     * দুইজন একসাথে দিলে একই নাম্বার তৈরি হওয়া ঠেকাতে unique constraint + retry ব্যবহার করা হয়েছে।
     */
    public function create(Doctor $doctor, User $by, array $data): Serial
    {
        $chamber = $doctor->activeChamber();
        $today = self::today();
        $date = $this->normalizeDate($data['serial_date'] ?? null);

        if ($date < $today) {
            throw new Exception('আগের তারিখে সিরিয়াল দেওয়া যায় না।');
        }
        if ($date > self::maxDate()) {
            throw new Exception('সর্বোচ্চ ' . self::MAX_ADVANCE_DAYS . ' দিন আগে পর্যন্ত সিরিয়াল দেওয়া যায়।');
        }

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            try {
                return DB::transaction(function () use ($doctor, $by, $data, $chamber, $date) {

                    $patient = $this->patients->findOrCreate($doctor->id, [
                        'name' => $data['patient_name'],
                        'mobile' => $data['patient_mobile'],
                        'age' => $data['patient_age'] ?? null,
                        'weight' => $data['patient_weight'] ?? null,
                        'gender' => $data['patient_gender'] ?? null,
                    ]);

                    $existing = Serial::where('doctor_id', $doctor->id)
                        ->where('serial_date', $date)
                        ->where('patient_id', $patient->id)
                        ->whereIn('status', ['waiting', 'in_progress'])
                        ->first();

                    if ($existing) {
                        $label = Carbon::parse($date)->format('d M Y');
                        throw new Exception("এই রোগীর {$label} তারিখের সিরিয়াল (#{$existing->serial_number}) আগে থেকেই আছে।");
                    }

                    $next = (int) Serial::where('doctor_id', $doctor->id)
                        ->where('serial_date', $date)
                        ->max('serial_number') + 1;

                    return Serial::create([
                        'doctor_id' => $doctor->id,
                        'chamber_id' => $chamber?->id,
                        'patient_id' => $patient->id,
                        'created_by' => $by->id,
                        'serial_date' => $date,
                        'serial_number' => $next,
                        'status' => 'waiting',
                        'reason' => $data['reason'] ?? null,
                        'bp' => $data['bp'] ?? null,
                        'pulse' => $data['pulse'] ?? null,
                        'temperature' => $data['temperature'] ?? null,
                    ]);
                });
            } catch (UniqueConstraintViolationException $e) {
                if ($attempt === 5) {
                    throw new Exception('সিরিয়াল তৈরি করা যায়নি, আবার চেষ্টা করুন।');
                }
            }
        }

        throw new Exception('সিরিয়াল তৈরি করা যায়নি, আবার চেষ্টা করুন।');
    }

    /** শুধু waiting / skipped / cancelled / in_progress সেট করা যায়। done শুধু প্রেসক্রিপশন সেভ হলে হয়। */
    public function changeStatus(Serial $serial, string $status): Serial
    {
        if (!in_array($status, ['waiting', 'in_progress', 'skipped', 'cancelled'])) {
            throw new Exception('অবৈধ স্ট্যাটাস।');
        }

        if ($serial->status === 'done') {
            throw new Exception('সম্পন্ন সিরিয়ালের স্ট্যাটাস বদলানো যায় না।');
        }

        if ($serial->status === 'cancelled') {
            throw new Exception('বাতিল সিরিয়াল আর চালু করা যায় না। নতুন সিরিয়াল দিন।');
        }

        $serial->update([
            'status' => $status,
            'called_at' => $status === 'in_progress' ? now() : $serial->called_at,
        ]);

        return $serial;
    }

    /**
     * Doctor "Start" চাপলে: এই সিরিয়াল "চলছে" হয়।
     * একসাথে একটাই সিরিয়াল "চলছে" থাকে, আগেরটা (সেভ না হলে) আবার "অপেক্ষায়" ফিরে যায়।
     * শুধু আজকের খোলা সিরিয়ালে Start করা যায়।
     */
    public function start(Serial $serial): void
    {
        if (!$serial->isOpen()) {
            throw new Exception('এই সিরিয়ালটি আর চালু নেই (' . $serial->statusLabel() . ')।');
        }

        if ($serial->serial_date->toDateString() !== self::today()) {
            throw new Exception('এটি আজকের সিরিয়াল নয় (' . $serial->serial_date->format('d M Y') . ')। শুধু আজকের সিরিয়ালে Start করা যায়।');
        }

        DB::transaction(function () use ($serial) {
            Serial::where('doctor_id', $serial->doctor_id)
                ->where('serial_date', $serial->serial_date->toDateString())
                ->where('status', 'in_progress')
                ->where('id', '!=', $serial->id)
                ->update(['status' => 'waiting']);

            if (in_array($serial->status, ['waiting', 'skipped'])) {
                $serial->update(['status' => 'in_progress', 'called_at' => now()]);
            }
        });
    }

    public function markInProgress(Serial $serial): void
    {
        $this->start($serial);
    }

    public function complete(Serial $serial, Prescription $prescription): void
    {
        if (!in_array($serial->status, ['done', 'cancelled'])) {
            $serial->update(['status' => 'done', 'prescription_id' => $prescription->id]);
        }
    }
}