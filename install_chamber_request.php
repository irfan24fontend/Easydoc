<?php
/**
 * EasyDoc: Chamber Request feature installer
 *
 * Project root e (jekhane `artisan` file ache) ei file ta rekhe chalan:
 *     php install_chamber_request.php
 *     php install_chamber_request.php --migrate     (migrate + cache clear shoho)
 *
 * Ki kore:
 *  - migration, model, service, request, 2 controller, 4 blade file toiri kore
 *  - routes/web.php te `use` line ar admin + doctor route auto boshay
 *  - Chamber.php ar web.php er backup rakhe (*.bak-chamber)
 *  - dobar chalale duplicate kore na
 */

if (! file_exists(__DIR__ . '/artisan')) {
    fwrite(STDERR, "ERROR: `artisan` file paoa jayni. Ei file ta Laravel project er root folder e rekhe chalan.\n");
    exit(1);
}

$root = __DIR__;
$migrate = in_array('--migrate', $argv ?? [], true);

$files = [];

$files['database/migrations/2026_10_19_120000_add_request_fields_to_chambers_table.php'] = <<<'CHAMBER_EOF'
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

CHAMBER_EOF;

$files['app/Models/Chamber.php'] = <<<'CHAMBER_EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Chamber extends Model
{
    public const REQUEST_WAITING  = 'waiting_request';
    public const REQUEST_APPROVED = 'approved';
    public const REQUEST_REJECTED = 'rejected';

    protected $fillable = [
        'doctor_id',
        'prescription_template_id',
        'chamber_name',
        'hospital_name',
        'phone',
        'email',
        'address',
        'visiting_days',
        'visiting_time',
        'status',          // Super Admin-er onumoti (true = active)
        'request_status',  // waiting_request | approved | rejected
        'doctor_active',   // Doctor-er nijer switch
        'pad_image',
        'requested_at',
        'reviewed_at',
        'reviewed_by',
        'reject_reason',
        'fee_charged',
    ];

    protected $casts = [
        'status'        => 'boolean',
        'doctor_active' => 'boolean',
        'requested_at'  => 'datetime',
        'reviewed_at'   => 'datetime',
        'fee_charged'   => 'decimal:2',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function template()
    {
        return $this->belongsTo(PrescriptionTemplate::class, 'prescription_template_id');
    }

    /** Chamber ekhon prescription-er jonno chalu (Super Admin Active + Doctor chalu) */
    public function isOperational(): bool
    {
        return $this->request_status === self::REQUEST_APPROVED
            && $this->status
            && $this->doctor_active;
    }

    public function scopeOperational(Builder $query): Builder
    {
        return $query->where('request_status', self::REQUEST_APPROVED)
            ->where('status', true)
            ->where('doctor_active', true);
    }

    /** UI-te dekhanor status: active | inactive | waiting_request | rejected */
    public function getDisplayStatusAttribute(): string
    {
        if ($this->request_status === self::REQUEST_WAITING) {
            return 'waiting_request';
        }
        if ($this->request_status === self::REQUEST_REJECTED) {
            return 'rejected';
        }

        return $this->status ? 'active' : 'inactive';
    }
}

CHAMBER_EOF;

$files['app/Services/ChamberRequestService.php'] = <<<'CHAMBER_EOF'
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

CHAMBER_EOF;

$files['app/Http/Requests/Doctor/StoreChamberRequestRequest.php'] = <<<'CHAMBER_EOF'
<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class StoreChamberRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'chamber_name'    => ['required', 'string', 'max:255'],
            'hospital_name'   => ['required', 'string', 'max:255'],
            'phone'           => ['required', 'string', 'max:30'],
            'email'           => ['nullable', 'email', 'max:255'],
            'address'         => ['required', 'string', 'max:1000'],
            'visiting_days'   => ['required', 'array', 'min:1'],
            'visiting_days.*' => ['string', 'in:শনি,রবি,সোম,মঙ্গল,বুধ,বৃহস্পতি,শুক্র'],
            'visiting_time'   => ['required', 'string', 'max:100'],
            'pad_image'       => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function attributes(): array
    {
        return [
            'chamber_name'  => 'চেম্বারের নাম',
            'hospital_name' => 'হাসপাতাল/ক্লিনিকের নাম',
            'phone'         => 'ফোন নম্বর',
            'address'       => 'ঠিকানা',
            'visiting_days' => 'বসার দিন',
            'visiting_time' => 'বসার সময়',
            'pad_image'     => 'প্রেসক্রিপশন প্যাডের ছবি',
        ];
    }
}

CHAMBER_EOF;

$files['app/Http/Controllers/Doctor/ChamberRequestController.php'] = <<<'CHAMBER_EOF'
<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\StoreChamberRequestRequest;
use App\Services\ChamberRequestService;
use Illuminate\Http\Request;
use Throwable;

class ChamberRequestController extends Controller
{
    public function __construct(private ChamberRequestService $service)
    {
    }

    public function create(Request $request)
    {
        $doctor = $request->user()->doctor;
        $wallet = $this->service->walletFor($doctor);
        $fee    = ChamberRequestService::FEE;

        return view('doctor.chambers.request', [
            'balance'    => $wallet->balance,
            'fee'        => $fee,
            'hasBalance' => $wallet->balance >= $fee,
        ]);
    }

    public function store(StoreChamberRequestRequest $request)
    {
        $doctor = $request->user()->doctor;

        try {
            $this->service->submit(
                $doctor,
                $request->validated(),
                $request->file('pad_image')
            );
        } catch (Throwable $e) {
            return back()->withInput()->withErrors(['balance' => $e->getMessage()]);
        }

        return redirect()
            ->route('doctor.chambers.index')
            ->with('success', 'চেম্বার রিকোয়েস্ট পাঠানো হয়েছে। সুপার এডমিন রিভিউ করার পর চেম্বার চালু হবে।');
    }
}

CHAMBER_EOF;

$files['app/Http/Controllers/Admin/ChamberRequestController.php'] = <<<'CHAMBER_EOF'
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chamber;
use App\Models\PrescriptionTemplate;
use App\Services\ChamberRequestService;
use Illuminate\Http\Request;
use Throwable;

class ChamberRequestController extends Controller
{
    public function __construct(private ChamberRequestService $service)
    {
    }

    public function index(Request $request)
    {
        $filter = $request->query('filter', 'waiting_request');

        $query = Chamber::with('doctor')->orderByDesc('id');

        match ($filter) {
            'waiting_request' => $query->where('request_status', Chamber::REQUEST_WAITING),
            'active'          => $query->where('request_status', Chamber::REQUEST_APPROVED)->where('status', true),
            'inactive'        => $query->where('request_status', Chamber::REQUEST_APPROVED)->where('status', false),
            'rejected'        => $query->where('request_status', Chamber::REQUEST_REJECTED),
            default           => null,
        };

        $counts = [
            'waiting_request' => Chamber::where('request_status', Chamber::REQUEST_WAITING)->count(),
            'active'          => Chamber::where('request_status', Chamber::REQUEST_APPROVED)->where('status', true)->count(),
            'inactive'        => Chamber::where('request_status', Chamber::REQUEST_APPROVED)->where('status', false)->count(),
            'rejected'        => Chamber::where('request_status', Chamber::REQUEST_REJECTED)->count(),
            'all'             => Chamber::count(),
        ];

        return view('admin.chamber-requests.index', [
            'chambers' => $query->paginate(20)->withQueryString(),
            'filter'   => $filter,
            'counts'   => $counts,
        ]);
    }

    public function show(Chamber $chamber)
    {
        $chamber->load(['doctor', 'template']);

        return view('admin.chamber-requests.show', [
            'chamber'   => $chamber,
            'templates' => PrescriptionTemplate::where('status', true)->orderBy('name')->get(),
            'fee'       => ChamberRequestService::FEE,
        ]);
    }

    public function approve(Request $request, Chamber $chamber)
    {
        $data = $request->validate([
            'prescription_template_id' => ['required', 'integer', 'exists:prescription_templates,id'],
        ]);

        try {
            $this->service->approve($chamber, (int) $data['prescription_template_id'], $request->user()->id);
        } catch (Throwable $e) {
            return back()->withErrors(['approve' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.chamber-requests.index')
            ->with('success', 'চেম্বার Active করা হয়েছে।');
    }

    public function reject(Request $request, Chamber $chamber)
    {
        $data = $request->validate([
            'reject_reason' => ['required', 'string', 'max:255'],
        ]);

        try {
            $this->service->reject($chamber, $data['reject_reason'], $request->user()->id);
        } catch (Throwable $e) {
            return back()->withErrors(['reject' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.chamber-requests.index')
            ->with('success', 'রিকোয়েস্ট বাতিল করা হয়েছে এবং ফি ফেরত দেওয়া হয়েছে।');
    }

    public function toggle(Chamber $chamber)
    {
        try {
            $this->service->toggleStatus($chamber);
        } catch (Throwable $e) {
            return back()->withErrors(['toggle' => $e->getMessage()]);
        }

        return back()->with('success', 'স্ট্যাটাস আপডেট হয়েছে।');
    }
}

CHAMBER_EOF;

$files['resources/views/partials/chamber-status-badge.blade.php'] = <<<'CHAMBER_EOF'
@php
    $map = [
        'active'          => ['Active',          'bg-emerald-50 text-emerald-700 ring-emerald-600/20'],
        'inactive'        => ['Inactive',        'bg-slate-100 text-slate-600 ring-slate-500/20'],
        'waiting_request' => ['Waiting request', 'bg-amber-50 text-amber-700 ring-amber-600/20'],
        'rejected'        => ['Rejected',        'bg-rose-50 text-rose-700 ring-rose-600/20'],
    ];
    [$label, $classes] = $map[$status] ?? [$status, 'bg-slate-100 text-slate-600 ring-slate-500/20'];
@endphp
<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $classes }}">{{ $label }}</span>

CHAMBER_EOF;

$files['resources/views/doctor/chambers/request.blade.php'] = <<<'CHAMBER_EOF'
@extends('layouts.doctor')

@section('title', 'নতুন চেম্বার রিকোয়েস্ট')

@push('styles')
<style>
    :root { --rq-brand: #4f46e5; --rq-ink: #0f172a; --rq-muted: #64748b; --rq-line: #e5e9f0; }

    /* ---------- Hero ---------- */
    .rq-hero {
        position: relative; overflow: hidden; border-radius: 22px; padding: 1.5rem 1.8rem; color: #fff;
        background: radial-gradient(1200px 300px at 90% -20%, rgba(255,255,255,.18), transparent 60%),
                    linear-gradient(135deg, #4f46e5 0%, #6d28d9 55%, #7c3aed 100%);
        box-shadow: 0 14px 34px rgba(79,70,229,.28);
    }
    .rq-hero::after { content: ""; position: absolute; right: -60px; bottom: -80px; width: 240px; height: 240px; border-radius: 50%; background: rgba(255,255,255,.07); }
    .rq-hero h1 { font-size: 1.5rem; font-weight: 800; letter-spacing: -.02em; margin: 0; }
    .rq-hero p { margin: .3rem 0 0; opacity: .85; font-size: .88rem; max-width: 560px; }
    .rq-back { position: relative; z-index: 1; display: inline-flex; align-items: center; gap: 8px; color: #fff; text-decoration: none; font-weight: 700; font-size: .82rem; padding: .5rem .95rem; border-radius: 12px; background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.28); transition: background .15s; }
    .rq-back:hover { background: rgba(255,255,255,.24); color: #fff; }

    /* ---------- Section cards ---------- */
    .rq-card { background: #fff; border: 1px solid var(--rq-line); border-radius: 20px; padding: 1.35rem 1.4rem; box-shadow: 0 1px 3px rgba(15,23,42,.05); }
    .rq-card + .rq-card { margin-top: 1.25rem; }
    .rq-card-head { display: flex; align-items: center; gap: 12px; margin-bottom: 1.1rem; }
    .rq-card-head .ico { width: 40px; height: 40px; border-radius: 12px; background: #eef2ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: .95rem; flex-shrink: 0; }
    .rq-card-head h2 { margin: 0; font-size: 1rem; font-weight: 800; color: var(--rq-ink); }
    .rq-card-head p { margin: 0; font-size: .78rem; color: var(--rq-muted); }

    .rq-card .form-label { font-size: .8rem; font-weight: 700; color: #334155; margin-bottom: .35rem; }
    .rq-card .form-label .opt { font-weight: 500; color: #94a3b8; }
    .rq-card .form-control { border: 1px solid #e2e8f0; border-radius: 12px; padding: .6rem .9rem; font-size: .9rem; background: #f8fafc; transition: all .15s; }
    .rq-card .form-control:focus { background: #fff; border-color: var(--rq-brand); box-shadow: 0 0 0 4px rgba(79,70,229,.1); }
    .rq-card .form-control.is-invalid { border-color: #f87171; background: #fffafa; }
    .rq-card .form-control.is-invalid:focus { box-shadow: 0 0 0 4px rgba(248,113,113,.15); }

    /* ---------- Day chips ---------- */
    .rq-days { display: flex; flex-wrap: wrap; gap: .5rem; }
    .rq-day { position: relative; margin: 0; cursor: pointer; }
    .rq-day input { position: absolute; opacity: 0; inset: 0; width: 100%; height: 100%; margin: 0; cursor: pointer; }
    .rq-day span { display: inline-block; padding: .45rem .95rem; border-radius: 999px; border: 1px solid #e2e8f0; background: #fff; color: #475569; font-size: .82rem; font-weight: 700; transition: all .15s; }
    .rq-day:hover span { background: #eef2ff; border-color: #c7d2fe; color: #4338ca; }
    .rq-day input:checked + span { background: var(--rq-brand); border-color: var(--rq-brand); color: #fff; box-shadow: 0 4px 12px rgba(79,70,229,.25); }
    .rq-day input:focus-visible + span { box-shadow: 0 0 0 4px rgba(79,70,229,.25); }

    /* ---------- Dropzone ---------- */
    .rq-drop { display: block; border: 2px dashed #cbd5e1; border-radius: 16px; padding: 1.8rem 1rem; text-align: center; background: #f8fafc; cursor: pointer; transition: all .15s; margin: 0; }
    .rq-drop:hover, .rq-drop.drag { border-color: var(--rq-brand); background: #eef2ff; }
    .rq-drop.is-invalid { border-color: #f87171; background: #fffafa; }
    .rq-drop .big { width: 52px; height: 52px; border-radius: 16px; background: #eef2ff; color: #4f46e5; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; margin-bottom: .6rem; }
    .rq-drop .t1 { font-weight: 800; color: var(--rq-ink); font-size: .92rem; }
    .rq-drop .t2 { font-size: .76rem; color: var(--rq-muted); margin-top: .15rem; }
    .rq-drop input { display: none; }

    .rq-preview { display: none; align-items: flex-start; gap: 14px; border: 1px solid var(--rq-line); border-radius: 16px; padding: .75rem; background: #fff; }
    .rq-preview.show { display: flex; }
    .rq-preview img { width: 120px; height: 150px; object-fit: cover; border-radius: 12px; border: 1px solid #e2e8f0; background: #f1f5f9; }
    .rq-preview .meta { min-width: 0; flex: 1; padding-top: .2rem; }
    .rq-preview .nm { font-weight: 700; font-size: .86rem; color: var(--rq-ink); word-break: break-all; }
    .rq-preview .sz { font-size: .76rem; color: var(--rq-muted); }
    .rq-preview .rm { margin-top: .6rem; border: 1px solid #fecaca; background: #fef2f2; color: #b91c1c; font-size: .76rem; font-weight: 700; padding: .3rem .7rem; border-radius: 9px; cursor: pointer; }
    .rq-preview .rm:hover { background: #fee2e2; }

    /* ---------- Sidebar ---------- */
    .rq-side { position: sticky; top: 88px; }
    .rq-wallet { border-radius: 20px; padding: 1.25rem 1.3rem; background: #fff; border: 1px solid var(--rq-line); box-shadow: 0 1px 3px rgba(15,23,42,.05); }
    .rq-wallet.low { border-color: #fecaca; background: #fffafa; }
    .rq-wallet .lbl { font-size: .72rem; font-weight: 700; color: var(--rq-muted); text-transform: uppercase; letter-spacing: .06em; }
    .rq-wallet .bal { font-size: 1.7rem; font-weight: 800; color: var(--rq-ink); line-height: 1.1; margin-top: .15rem; }
    .rq-wallet.low .bal { color: #b91c1c; }
    .rq-lines { margin: 1rem 0 0; padding: .85rem 0 0; border-top: 1px dashed #e2e8f0; }
    .rq-line { display: flex; justify-content: space-between; font-size: .84rem; color: #475569; padding: .22rem 0; }
    .rq-line b { color: var(--rq-ink); }
    .rq-line.total { border-top: 1px solid #e2e8f0; margin-top: .35rem; padding-top: .6rem; font-weight: 800; }
    .rq-line.total b.ok { color: #15803d; }
    .rq-line.total b.bad { color: #b91c1c; }
    .rq-warn { margin-top: .9rem; font-size: .8rem; color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: .6rem .75rem; }

    .rq-submit { width: 100%; margin-top: 1rem; border: 0; border-radius: 14px; padding: .8rem 1rem; font-weight: 800; font-size: .92rem; color: #fff; background: linear-gradient(135deg, #4f46e5, #7c3aed); box-shadow: 0 10px 22px rgba(79,70,229,.3); transition: transform .15s, box-shadow .15s; display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
    .rq-submit:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 14px 28px rgba(79,70,229,.36); }
    .rq-submit:disabled { background: #cbd5e1; box-shadow: none; cursor: not-allowed; }
    .rq-submit .spin { display: none; width: 16px; height: 16px; border-radius: 50%; border: 2px solid rgba(255,255,255,.4); border-top-color: #fff; animation: rqSpin .7s linear infinite; }
    .rq-submit.busy .spin { display: inline-block; }
    @keyframes rqSpin { to { transform: rotate(360deg); } }

    .rq-steps { margin-top: 1.25rem; background: #fff; border: 1px solid var(--rq-line); border-radius: 20px; padding: 1.15rem 1.3rem; }
    .rq-steps h3 { font-size: .86rem; font-weight: 800; color: var(--rq-ink); margin: 0 0 .8rem; }
    .rq-step { display: flex; gap: 11px; position: relative; padding-bottom: .9rem; }
    .rq-step:last-child { padding-bottom: 0; }
    .rq-step:not(:last-child)::before { content: ""; position: absolute; left: 13px; top: 28px; bottom: 2px; width: 2px; background: #e0e7ff; }
    .rq-step .n { width: 28px; height: 28px; border-radius: 50%; background: #eef2ff; color: #4338ca; font-weight: 800; font-size: .78rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .rq-step .tx { font-size: .8rem; color: #475569; line-height: 1.45; padding-top: 2px; }
    .rq-step .tx b { color: var(--rq-ink); display: block; }

    .rq-alert { border: 0; border-radius: 14px; }

    @media (max-width: 991.98px) { .rq-side { position: static; } }
    @media (prefers-reduced-motion: reduce) { .rq-submit .spin { animation: none; } }
</style>
@endpush

@section('content')
@php
    $balanceNum = (float) $balance;
    $feeNum     = (float) $fee;
    $after      = $balanceNum - $feeNum;
    $short      = $feeNum - $balanceNum;
    $dayOptions = ['শনি', 'রবি', 'সোম', 'মঙ্গল', 'বুধ', 'বৃহস্পতি', 'শুক্র'];
@endphp

<div class="py-2">
    <div class="container-fluid px-0">

        {{-- ================= Hero ================= --}}
        <div class="rq-hero mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 position-relative" style="z-index:1;">
                <div>
                    <h1>নতুন চেম্বার রিকোয়েস্ট</h1>
                    <p>চেম্বারের তথ্য ও প্রেসক্রিপশন প্যাডের ছবি দিন। Super Admin রিভিউ করে প্যাড অনুযায়ী টেমপ্লেট সেট করে চেম্বার Active করবেন।</p>
                </div>
                <a href="{{ route('doctor.chambers.index') }}" class="rq-back"><i class="fa-solid fa-arrow-left"></i> আমার চেম্বার</a>
            </div>
        </div>

        {{-- ================= Errors ================= --}}
        @if($errors->any())
            <div class="alert alert-danger rq-alert shadow-sm d-flex align-items-start gap-2 mb-4">
                <i class="fa-solid fa-circle-exclamation mt-1"></i>
                <div>
                    @if($errors->has('balance'))
                        <b>{{ $errors->first('balance') }}</b>
                    @else
                        <b>কিছু তথ্য ঠিক নেই।</b> নিচে লাল চিহ্নিত ঘরগুলো ঠিক করে আবার পাঠান।
                    @endif
                </div>
            </div>
        @endif

        <form id="rqForm" method="POST" action="{{ route('doctor.chambers.request.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="row g-4">

                {{-- ================= Left: form ================= --}}
                <div class="col-lg-8">

                    <div class="rq-card">
                        <div class="rq-card-head">
                            <div class="ico"><i class="fa-solid fa-hospital"></i></div>
                            <div><h2>চেম্বারের তথ্য</h2><p>যে নামে চেম্বার প্রেসক্রিপশনে দেখাবে।</p></div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="chamber_name">চেম্বারের নাম</label>
                                <input type="text" id="chamber_name" name="chamber_name" value="{{ old('chamber_name') }}"
                                       class="form-control @error('chamber_name') is-invalid @enderror" placeholder="যেমন: সিটি কেয়ার চেম্বার" required>
                                @error('chamber_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="hospital_name">হাসপাতাল / ক্লিনিকের নাম</label>
                                <input type="text" id="hospital_name" name="hospital_name" value="{{ old('hospital_name') }}"
                                       class="form-control @error('hospital_name') is-invalid @enderror" required>
                                @error('hospital_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="rq-card">
                        <div class="rq-card-head">
                            <div class="ico"><i class="fa-solid fa-location-dot"></i></div>
                            <div><h2>যোগাযোগ ও ঠিকানা</h2><p>রোগীরা এই তথ্য দেখে যোগাযোগ করবে।</p></div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="phone">ফোন নম্বর</label>
                                <input type="text" id="phone" name="phone" value="{{ old('phone') }}" inputmode="tel"
                                       class="form-control @error('phone') is-invalid @enderror" placeholder="01XXXXXXXXX" required>
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="email">ইমেইল <span class="opt">(ঐচ্ছিক)</span></label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}"
                                       class="form-control @error('email') is-invalid @enderror">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="address">ঠিকানা</label>
                                <textarea id="address" name="address" rows="2"
                                          class="form-control @error('address') is-invalid @enderror" required>{{ old('address') }}</textarea>
                                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="rq-card">
                        <div class="rq-card-head">
                            <div class="ico"><i class="fa-solid fa-calendar-days"></i></div>
                            <div><h2>বসার দিন ও সময়</h2><p>যে দিনগুলোতে চেম্বারে বসেন সেগুলো বেছে নিন।</p></div>
                        </div>

                        <label class="form-label">বসার দিন</label>
                        <div class="rq-days" id="rqDays">
                            @foreach($dayOptions as $day)
                                <label class="rq-day">
                                    <input type="checkbox" name="visiting_days[]" value="{{ $day }}" @checked(in_array($day, old('visiting_days', [])))>
                                    <span>{{ $day }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="text-danger small mt-2 {{ $errors->has('visiting_days') ? '' : 'd-none' }}" id="rqDaysErr">
                            {{ $errors->first('visiting_days') ?: 'অন্তত একটি দিন বেছে নিন।' }}
                        </div>

                        <div class="mt-3">
                            <label class="form-label" for="visiting_time">বসার সময়</label>
                            <input type="text" id="visiting_time" name="visiting_time" value="{{ old('visiting_time') }}"
                                   class="form-control @error('visiting_time') is-invalid @enderror" placeholder="বিকাল ৫:০০ - রাত ৯:০০" required>
                            @error('visiting_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="rq-card">
                        <div class="rq-card-head">
                            <div class="ico"><i class="fa-solid fa-file-image"></i></div>
                            <div><h2>প্রেসক্রিপশন প্যাডের ছবি</h2><p>আপনার বর্তমান প্যাডের পরিষ্কার ছবি দিন। এই ছবি দেখে টেমপ্লেট বানানো হবে।</p></div>
                        </div>

                        <label class="rq-drop @error('pad_image') is-invalid @enderror" id="rqDrop" for="pad_image">
                            <div class="big"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                            <div class="t1">ছবি বেছে নিন বা এখানে টেনে আনুন</div>
                            <div class="t2">JPG, PNG বা WEBP, সর্বোচ্চ ৪ MB</div>
                            <input type="file" id="pad_image" name="pad_image" accept="image/jpeg,image/png,image/webp" required>
                        </label>
                        @error('pad_image')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        <div class="text-danger small mt-2 d-none" id="rqFileErr"></div>

                        <div class="rq-preview mt-3" id="rqPreview">
                            <img id="rqPreviewImg" alt="প্যাডের প্রিভিউ">
                            <div class="meta">
                                <div class="nm" id="rqFileName"></div>
                                <div class="sz" id="rqFileSize"></div>
                                <button type="button" class="rm" id="rqRemove"><i class="fa-solid fa-trash-can me-1"></i> সরিয়ে দিন</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ================= Right: wallet + submit ================= --}}
                <div class="col-lg-4">
                    <div class="rq-side">

                        <div class="rq-wallet {{ $hasBalance ? '' : 'low' }}">
                            <div class="lbl">বর্তমান ব্যালেন্স</div>
                            <div class="bal">৳ {{ number_format($balanceNum, 2) }}</div>

                            <div class="rq-lines">
                                <div class="rq-line"><span>চেম্বার রিকোয়েস্ট ফি</span><b>− ৳ {{ number_format($feeNum, 2) }}</b></div>
                                <div class="rq-line total">
                                    <span>ফি কাটার পর ব্যালেন্স</span>
                                    <b class="{{ $hasBalance ? 'ok' : 'bad' }}">৳ {{ number_format(max($after, 0), 2) }}</b>
                                </div>
                            </div>

                            @unless($hasBalance)
                                <div class="rq-warn">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                    ব্যালেন্স কম। আরও <b>৳{{ number_format($short, 2) }}</b> লাগবে। Super Admin-এর কাছ থেকে রিচার্জ করিয়ে নিন।
                                </div>
                            @endunless

                            <button type="submit" class="rq-submit" id="rqSubmit" {{ $hasBalance ? '' : 'disabled' }}>
                                <span class="spin"></span>
                                <i class="fa-solid fa-paper-plane"></i>
                                <span id="rqSubmitText">রিকোয়েস্ট পাঠান</span>
                            </button>
                        </div>

                        <div class="rq-steps">
                            <h3>এরপর কী হবে</h3>
                            <div class="rq-step"><div class="n">১</div><div class="tx"><b>রিকোয়েস্ট জমা</b>ফি কাটা হবে এবং চেম্বার "অপেক্ষমান" থাকবে।</div></div>
                            <div class="rq-step"><div class="n">২</div><div class="tx"><b>Super Admin রিভিউ</b>প্যাড দেখে আপনার জন্য টেমপ্লেট তৈরি করবেন।</div></div>
                            <div class="rq-step"><div class="n">৩</div><div class="tx"><b>চেম্বার Active</b>অনুমোদন হলে "আমার চেম্বার" থেকে চালু করে প্রেসক্রিপশন লিখুন। বাতিল হলে ফি ফেরত পাবেন।</div></div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const MAX = 4 * 1024 * 1024;
    const form = document.getElementById('rqForm');
    const input = document.getElementById('pad_image');
    const drop = document.getElementById('rqDrop');
    const preview = document.getElementById('rqPreview');
    const img = document.getElementById('rqPreviewImg');
    const nameEl = document.getElementById('rqFileName');
    const sizeEl = document.getElementById('rqFileSize');
    const fileErr = document.getElementById('rqFileErr');
    const submitBtn = document.getElementById('rqSubmit');
    const submitText = document.getElementById('rqSubmitText');
    const daysErr = document.getElementById('rqDaysErr');

    function fmt(bytes) { return bytes > 1048576 ? (bytes / 1048576).toFixed(1) + ' MB' : Math.round(bytes / 1024) + ' KB'; }

    function showError(msg) { fileErr.textContent = msg; fileErr.classList.toggle('d-none', !msg); drop.classList.toggle('is-invalid', !!msg); }

    function clearFile() {
        input.value = '';
        preview.classList.remove('show');
        if (img.src) { URL.revokeObjectURL(img.src); img.removeAttribute('src'); }
    }

    function handle(file) {
        if (!file) { clearFile(); return; }
        if (!/^image\/(jpeg|png|webp)$/.test(file.type)) { clearFile(); showError('শুধু JPG, PNG বা WEBP ছবি দিন।'); return; }
        if (file.size > MAX) { clearFile(); showError('ছবির আকার ৪ MB এর বেশি হতে পারবে না।'); return; }
        showError('');
        img.src = URL.createObjectURL(file);
        nameEl.textContent = file.name;
        sizeEl.textContent = fmt(file.size);
        preview.classList.add('show');
    }

    input.addEventListener('change', function () { handle(input.files[0]); });
    document.getElementById('rqRemove').addEventListener('click', function () { clearFile(); showError(''); });

    ['dragenter', 'dragover'].forEach(function (ev) {
        drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('drag'); });
    });
    ['dragleave', 'drop'].forEach(function (ev) {
        drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('drag'); });
    });
    drop.addEventListener('drop', function (e) {
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            input.files = e.dataTransfer.files;
            handle(input.files[0]);
        }
    });

    // Duplicate submit thamate (double click korle duibar 500 tk kete jete pare)
    let sending = false;
    form.addEventListener('submit', function (e) {
        if (sending) { e.preventDefault(); return; }

        if (!document.querySelector('#rqDays input:checked')) {
            e.preventDefault();
            daysErr.classList.remove('d-none');
            daysErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        daysErr.classList.add('d-none');

        sending = true;
        submitBtn.classList.add('busy');
        submitBtn.disabled = true;
        submitText.textContent = 'পাঠানো হচ্ছে...';
    });
})();
</script>
@endpush

CHAMBER_EOF;

$files['resources/views/admin/chamber-requests/index.blade.php'] = <<<'CHAMBER_EOF'
@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-6xl">

    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-900">Chamber requests</h1>
        <a href="{{ route('admin.chambers.index') }}" class="text-sm text-indigo-600 hover:underline">All chambers</a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
    @endif

    {{-- Filter tabs --}}
    <div class="mb-4 flex flex-wrap gap-2">
        @foreach(['waiting_request' => 'Waiting request', 'active' => 'Active', 'inactive' => 'Inactive', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label)
            <a href="{{ route('admin.chamber-requests.index', ['filter' => $key]) }}"
               class="rounded-lg px-3 py-1.5 text-sm {{ $filter === $key ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' }}">
                {{ $label }} <span class="{{ $filter === $key ? 'text-indigo-200' : 'text-slate-400' }}">{{ $counts[$key] }}</span>
            </a>
        @endforeach
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Doctor</th>
                    <th class="px-4 py-3 font-medium">Chamber</th>
                    <th class="px-4 py-3 font-medium">Requested</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($chambers as $chamber)
                    <tr>
                        <td class="px-4 py-3 text-slate-900">
                            {{ $chamber->doctor->name ?? ($chamber->doctor->user->name ?? 'Doctor #'.$chamber->doctor_id) }}
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-slate-900">{{ $chamber->chamber_name }}</p>
                            <p class="text-xs text-slate-500">{{ $chamber->hospital_name }}</p>
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ optional($chamber->requested_at)->format('d M Y, h:i A') ?? '-' }}</td>
                        <td class="px-4 py-3">@include('partials.chamber-status-badge', ['status' => $chamber->display_status])</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.chamber-requests.show', $chamber) }}" class="text-indigo-600 hover:underline">
                                {{ $chamber->request_status === 'waiting_request' ? 'Review' : 'View' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">No chambers in this view.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $chambers->links() }}</div>
</div>
@endsection

CHAMBER_EOF;

$files['resources/views/admin/chamber-requests/show.blade.php'] = <<<'CHAMBER_EOF'
@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-5xl">

    <div class="mb-5 flex items-center justify-between">
        <div>
            <a href="{{ route('admin.chamber-requests.index') }}" class="text-sm text-indigo-600 hover:underline">Back to requests</a>
            <h1 class="mt-1 text-xl font-semibold text-slate-900">{{ $chamber->chamber_name }}</h1>
        </div>
        @include('partials.chamber-status-badge', ['status' => $chamber->display_status])
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-5">

        {{-- Details --}}
        <div class="space-y-4 lg:col-span-2">
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-slate-500">Doctor</dt>
                        <dd class="text-slate-900">{{ $chamber->doctor->name ?? ($chamber->doctor->user->name ?? 'Doctor #'.$chamber->doctor_id) }}</dd></div>
                    <div><dt class="text-slate-500">Hospital / clinic</dt><dd class="text-slate-900">{{ $chamber->hospital_name }}</dd></div>
                    <div><dt class="text-slate-500">Phone</dt><dd class="text-slate-900">{{ $chamber->phone }}</dd></div>
                    @if($chamber->email)
                        <div><dt class="text-slate-500">Email</dt><dd class="text-slate-900">{{ $chamber->email }}</dd></div>
                    @endif
                    <div><dt class="text-slate-500">Address</dt><dd class="text-slate-900">{{ $chamber->address }}</dd></div>
                    <div><dt class="text-slate-500">Visiting days</dt><dd class="text-slate-900">{{ $chamber->visiting_days }}</dd></div>
                    <div><dt class="text-slate-500">Visiting time</dt><dd class="text-slate-900">{{ $chamber->visiting_time }}</dd></div>
                    <div><dt class="text-slate-500">Requested</dt>
                        <dd class="text-slate-900">{{ optional($chamber->requested_at)->format('d M Y, h:i A') ?? '-' }}</dd></div>
                    @if($chamber->template)
                        <div><dt class="text-slate-500">Assigned template</dt><dd class="text-slate-900">{{ $chamber->template->name }}</dd></div>
                    @endif
                    @if($chamber->reject_reason)
                        <div><dt class="text-slate-500">Reject reason</dt><dd class="text-rose-700">{{ $chamber->reject_reason }}</dd></div>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Pad image + actions --}}
        <div class="space-y-4 lg:col-span-3">
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <p class="mb-3 text-sm font-medium text-slate-700">Prescription pad</p>
                @if($chamber->pad_image)
                    <a href="{{ asset('storage/'.$chamber->pad_image) }}" target="_blank" rel="noopener">
                        <img src="{{ asset('storage/'.$chamber->pad_image) }}" alt="Prescription pad" class="max-h-[32rem] w-full rounded-lg border border-slate-200 object-contain">
                    </a>
                @else
                    <p class="text-sm text-slate-500">No pad image uploaded.</p>
                @endif
            </div>

            @if($chamber->request_status === 'waiting_request')
                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <p class="text-sm font-medium text-slate-700">Assign template and activate</p>
                    <p class="mt-1 text-xs text-slate-500">
                        Create a template from the pad image first, then pick it here.
                        @if(Route::has('admin.prescription-templates.create'))
                            <a href="{{ route('admin.prescription-templates.create') }}" class="text-indigo-600 hover:underline">Create template</a>
                        @endif
                    </p>

                    <form method="POST" action="{{ route('admin.chamber-requests.approve', $chamber) }}" class="mt-3 flex flex-wrap items-end gap-3">
                        @csrf
                        <div class="min-w-[14rem] flex-1">
                            <select name="prescription_template_id" class="w-full rounded-lg border-slate-300 text-sm" required>
                                <option value="">Select template</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" @selected(old('prescription_template_id') == $template->id)>{{ $template->name }} ({{ $template->paper_size }})</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Activate chamber</button>
                    </form>

                    <form method="POST" action="{{ route('admin.chamber-requests.reject', $chamber) }}" class="mt-5 border-t border-slate-100 pt-4">
                        @csrf
                        <label class="mb-1 block text-sm font-medium text-slate-700">Reject request</label>
                        <p class="mb-2 text-xs text-slate-500">৳{{ $fee }} will be refunded to the doctor's wallet.</p>
                        <div class="flex flex-wrap gap-3">
                            <input type="text" name="reject_reason" value="{{ old('reject_reason') }}" placeholder="Reason shown to the doctor" class="min-w-[14rem] flex-1 rounded-lg border-slate-300 text-sm" required>
                            <button class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700">Reject and refund</button>
                        </div>
                    </form>
                </div>

            @elseif($chamber->request_status === 'approved')
                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <form method="POST" action="{{ route('admin.chamber-requests.toggle', $chamber) }}">
                        @csrf @method('PATCH')
                        <button class="rounded-lg px-4 py-2 text-sm font-medium text-white {{ $chamber->status ? 'bg-slate-600 hover:bg-slate-700' : 'bg-emerald-600 hover:bg-emerald-700' }}">
                            {{ $chamber->status ? 'Set inactive' : 'Set active' }}
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

CHAMBER_EOF;

// ---------------------------------------------------------------------
// 1) File gulo likhi
// ---------------------------------------------------------------------
echo "== Files ==\n";

foreach ($files as $rel => $content) {
    $path = $root . '/' . $rel;

    // Migration: ager install thakle duplicate baniyo na
    if (str_starts_with($rel, 'database/migrations/')) {
        $existing = glob($root . '/database/migrations/*_add_request_fields_to_chambers_table.php');
        if (! empty($existing)) {
            echo "  skip   $rel (migration already ache)\n";
            continue;
        }
    }

    if (! is_dir(dirname($path))) {
        mkdir(dirname($path), 0755, true);
    }

    if (file_exists($path)) {
        $current = file_get_contents($path);
        if ($current === $content) {
            echo "  same   $rel\n";
            continue;
        }
        $bak = $path . '.bak-chamber';
        if (! file_exists($bak)) {
            copy($path, $bak);
        }
        echo "  update $rel (backup: " . basename($bak) . ")\n";
    } else {
        echo "  create $rel\n";
    }

    file_put_contents($path, $content);
}

// ---------------------------------------------------------------------
// 2) routes/web.php patch
// ---------------------------------------------------------------------
echo "\n== Routes ==\n";

$routesPath = $root . '/routes/web.php';

if (! file_exists($routesPath)) {
    fwrite(STDERR, "ERROR: routes/web.php paoa jayni.\n");
    exit(1);
}

$routes = file_get_contents($routesPath);

if (str_contains($routes, 'ChamberRequestController')) {
    echo "  skip   routes/web.php e ChamberRequestController already ache\n";
} else {
    $bak = $routesPath . '.bak-chamber';
    if (! file_exists($bak)) {
        copy($routesPath, $bak);
    }

    $missing = [];

    // 2a) use lines
    $useLines = "use App\\Http\\Controllers\\Admin\\ChamberRequestController as AdminChamberRequestController;\n"
              . "use App\\Http\\Controllers\\Doctor\\ChamberRequestController as DoctorChamberRequestController;\n";

    $count = 0;
    $routes = preg_replace_callback(
        '/^use App\\\\Http\\\\Controllers\\\\Doctor\\\\ChamberController as DoctorChamberController;\R/m',
        function ($m) use ($useLines) {
            return $m[0] . $useLines;
        },
        $routes, 1, $count
    );
    if ($count === 0) {
        // fallback: <?php er pore
        $routes = preg_replace('/^<\?php\R/', "<?php\n\n" . $useLines, $routes, 1, $count);
        if ($count === 0) {
            $missing[] = 'use lines';
        } else {
            echo "  note   use line gulo file er suru te boshano hoyeche\n";
        }
    }

    // 2b) admin routes: Route::resource('chambers', ChamberController::class);
    $count = 0;
    $routes = preg_replace_callback(
        '/^([ \t]*)Route::resource\(\s*[\'"]chambers[\'"]\s*,\s*ChamberController::class\s*\);[ \t]*\R/m',
        function ($m) {
            $i = $m[1];
            return $m[0]
                . "\n"
                . $i . "// Chamber requests (Super Admin review)\n"
                . $i . "Route::get('chamber-requests', [AdminChamberRequestController::class, 'index'])->name('chamber-requests.index');\n"
                . $i . "Route::get('chamber-requests/{chamber}', [AdminChamberRequestController::class, 'show'])\n"
                . $i . "    ->whereNumber('chamber')->name('chamber-requests.show');\n"
                . $i . "Route::post('chamber-requests/{chamber}/approve', [AdminChamberRequestController::class, 'approve'])\n"
                . $i . "    ->whereNumber('chamber')->name('chamber-requests.approve');\n"
                . $i . "Route::post('chamber-requests/{chamber}/reject', [AdminChamberRequestController::class, 'reject'])\n"
                . $i . "    ->whereNumber('chamber')->name('chamber-requests.reject');\n"
                . $i . "Route::patch('chamber-requests/{chamber}/toggle', [AdminChamberRequestController::class, 'toggle'])\n"
                . $i . "    ->whereNumber('chamber')->name('chamber-requests.toggle');\n";
        },
        $routes, 1, $count
    );
    if ($count === 0) {
        $missing[] = 'admin routes';
    }

    // 2c) doctor routes: Route::patch('chambers/{chamber}/toggle' ... ->name('chambers.toggle');
    $count = 0;
    $routes = preg_replace_callback(
        '/^([ \t]*)Route::patch\(\s*[\'"]chambers\/\{chamber\}\/toggle[\'"].*?->name\(\s*[\'"]chambers\.toggle[\'"]\s*\);[ \t]*\R/ms',
        function ($m) {
            $i = $m[1];
            return $m[0]
                . "\n"
                . $i . "// Chamber request (doctor)\n"
                . $i . "Route::get('chambers/request', [DoctorChamberRequestController::class, 'create'])->name('chambers.request.create');\n"
                . $i . "Route::post('chambers/request', [DoctorChamberRequestController::class, 'store'])->name('chambers.request.store');\n";
        },
        $routes, 1, $count
    );
    if ($count === 0) {
        $missing[] = 'doctor routes';
    }

    file_put_contents($routesPath, $routes);

    if (empty($missing)) {
        echo "  patched routes/web.php (use + admin + doctor routes)\n";
    } else {
        echo "  PARTIAL routes/web.php. Ei gulo auto boshano jayni: " . implode(', ', $missing) . "\n";
        echo "  Backup: routes/web.php.bak-chamber. Manually add korun (route snippet chat e dewa ache).\n";
    }
}

// ---------------------------------------------------------------------
// 3) Syntax check
// ---------------------------------------------------------------------
echo "\n== Syntax check ==\n";
$check = array_filter(array_keys($files), fn ($f) => str_ends_with($f, '.php'));
$check[] = 'routes/web.php';
$bad = 0;
foreach ($check as $rel) {
    $out = [];
    $code = 0;
    exec('php -l ' . escapeshellarg($root . '/' . $rel) . ' 2>&1', $out, $code);
    if ($code !== 0) {
        $bad++;
        echo "  ERROR  $rel\n    " . implode("\n    ", $out) . "\n";
    } else {
        echo "  ok     $rel\n";
    }
}

if ($bad > 0) {
    echo "\nSyntax error ache, upore dekhun. Backup (*.bak-chamber) theke restore kora jay.\n";
    exit(1);
}

// ---------------------------------------------------------------------
// 4) Optional migrate
// ---------------------------------------------------------------------
if ($migrate) {
    echo "\n== Artisan ==\n";
    passthru('php artisan migrate --no-interaction');
    passthru('php artisan optimize:clear');
    passthru('php artisan route:list --name=chamber');
} else {
    echo "\nShesh. Ekhon chalan:\n";
    echo "  php artisan migrate\n";
    echo "  php artisan optimize:clear\n";
    echo "  php artisan route:list --name=chamber\n";
}
