<?php

namespace App\Services;

use App\Models\Chamber;
use App\Models\Doctor;
use App\Models\PrescriptionTemplate;
use App\Models\Wallet;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ChamberRequestService
{
    public const FEE = 500;

    public function __construct(private WalletService $walletService)
    {
    }

    public function walletFor(Doctor $doctor): Wallet
    {
        return Wallet::firstOrCreate(
            ['doctor_id' => $doctor->id],
            ['balance' => 0]
        );
    }

    /**
     * Doctor notun chamber request pathay. Request-er shomoy-i 500 tk kate.
     */
    public function submit(Doctor $doctor, array $data, UploadedFile $pad): Chamber
    {
        $wallet = $this->walletFor($doctor);

        if (! $this->walletService->hasSufficientBalance($wallet, self::FEE)) {
            throw new Exception(
                'নতুন চেম্বার যোগ করতে কমপক্ষে ' . self::FEE . ' টাকা ব্যালেন্স লাগবে। অনুগ্রহ করে রিচার্জ করুন।'
            );
        }

        $path = $pad->store('chamber-pads', 'public');

        try {
            return DB::transaction(function () use ($doctor, $data, $wallet, $path) {

                $chamber = Chamber::create([
                    'doctor_id'      => $doctor->id,
                    'chamber_name'   => $data['chamber_name'],
                    'hospital_name'  => $data['hospital_name'],
                    'phone'          => $data['phone'],
                    'email'          => $data['email'] ?? null,
                    'address'        => $data['address'],
                    'visiting_days'  => implode(', ', $data['visiting_days']),
                    'visiting_time'  => $data['visiting_time'],
                    'pad_image'      => $path,
                    'status'         => false,
                    'doctor_active'  => false,
                    'request_status' => Chamber::REQUEST_WAITING,
                    'requested_at'   => now(),
                    'fee_charged'    => self::FEE,
                ]);

                $this->walletService->debit(
                    $wallet,
                    self::FEE,
                    'নতুন চেম্বার রিকোয়েস্ট ফি (#' . $chamber->id . ')'
                );

                return $chamber;
            });
        } catch (Throwable $e) {
            Storage::disk('public')->delete($path);
            throw $e;
        }
    }

    /**
     * Super Admin template assign kore chamber Active kore.
     */
    public function approve(Chamber $chamber, int $templateId, int $adminId): Chamber
    {
        return DB::transaction(function () use ($chamber, $templateId, $adminId) {

            $chamber = Chamber::lockForUpdate()->findOrFail($chamber->id);

            if ($chamber->request_status !== Chamber::REQUEST_WAITING) {
                throw new Exception('এই রিকোয়েস্টটি আগেই রিভিউ করা হয়েছে।');
            }

            PrescriptionTemplate::findOrFail($templateId);

            // Doctor-er ar kono chalu chamber na thakle eta-i auto "chalu" hobe
            $hasOtherOperational = Chamber::where('doctor_id', $chamber->doctor_id)
                ->where('id', '!=', $chamber->id)
                ->operational()
                ->exists();

            $chamber->update([
                'prescription_template_id' => $templateId,
                'request_status'           => Chamber::REQUEST_APPROVED,
                'status'                   => true,
                'doctor_active'            => ! $hasOtherOperational,
                'reviewed_at'              => now(),
                'reviewed_by'              => $adminId,
                'reject_reason'            => null,
            ]);

            return $chamber;
        });
    }

    /**
     * Reject korle 500 tk refund hoy.
     */
    public function reject(Chamber $chamber, string $reason, int $adminId): Chamber
    {
        return DB::transaction(function () use ($chamber, $reason, $adminId) {

            $chamber = Chamber::lockForUpdate()->findOrFail($chamber->id);

            if ($chamber->request_status !== Chamber::REQUEST_WAITING) {
                throw new Exception('এই রিকোয়েস্টটি আগেই রিভিউ করা হয়েছে।');
            }

            $refund = (float) $chamber->fee_charged;

            if ($refund > 0) {
                $wallet = Wallet::where('doctor_id', $chamber->doctor_id)->firstOrFail();

                $this->walletService->credit(
                    $wallet,
                    $refund,
                    'চেম্বার রিকোয়েস্ট বাতিল, ফি ফেরত (#' . $chamber->id . ')'
                );
            }

            $chamber->update([
                'request_status' => Chamber::REQUEST_REJECTED,
                'status'         => false,
                'doctor_active'  => false,
                'fee_charged'    => 0,   // double refund ঠেকাতে
                'reviewed_at'    => now(),
                'reviewed_by'    => $adminId,
                'reject_reason'  => $reason,
            ]);

            return $chamber;
        });
    }

    /**
     * Approved chamber-er Active/Inactive toggle (Super Admin).
     */
    public function toggleStatus(Chamber $chamber): Chamber
    {
        if ($chamber->request_status !== Chamber::REQUEST_APPROVED) {
            throw new Exception('শুধু অনুমোদিত চেম্বারের স্ট্যাটাস পরিবর্তন করা যায়।');
        }

        $newStatus = ! $chamber->status;

        $chamber->update([
            'status'        => $newStatus,
            'doctor_active' => $newStatus ? $chamber->doctor_active : false,
        ]);

        return $chamber;
    }
}
