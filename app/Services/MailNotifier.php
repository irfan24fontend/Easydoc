<?php

namespace App\Services;

use App\Mail\NoticeMail;
use App\Models\Chamber;
use App\Models\Doctor;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

/**
 * Shob email pathanor ekta-i jaygа.
 * - Response er por (afterResponse) pathay, tai page slow hoy na.
 * - Mail fail korle app bhange na, shudhu laravel.log e warning likhe.
 */
class MailNotifier
{
    /* ================================================================
     |  Low level
     ================================================================ */

    public function send(string|array|null $to, string $subject, array $payload): void
    {
        $to = array_values(array_unique(array_filter((array) $to)));

        if (empty($to)) {
            return;
        }

        $payload['appUrl'] = $payload['appUrl'] ?? config('app.url');

        dispatch(function () use ($to, $subject, $payload) {
            foreach ($to as $address) {
                try {
                    Mail::to($address)->send(new NoticeMail($subject, $payload));
                } catch (\Throwable $e) {
                    Log::warning('Email pathano gelo na', [
                        'to'      => $address,
                        'subject' => $subject,
                        'error'   => $e->getMessage(),
                    ]);
                }
            }
        })->afterResponse();
    }

    public function adminEmails(): array
    {
        $list = config('mailnotify.admin_emails', []);

        if (empty($list) && config('mail.from.address')) {
            $list = [config('mail.from.address')];
        }

        return $list;
    }

    /** Super Admin der email */
    public function toAdmins(string $subject, array $payload): void
    {
        $payload['greeting'] = $payload['greeting'] ?? 'Hello Admin,';
        $this->send($this->adminEmails(), $subject, $payload);
    }

    /** Je kono user ke (email + naam diye) */
    public function toPerson(?string $email, ?string $name, string $subject, array $payload): void
    {
        $payload['greeting'] = $payload['greeting'] ?? ('প্রিয় ' . ($name ?: 'ব্যবহারকারী') . ',');
        $this->send($email, $subject, $payload);
    }

    /** Notification / news module theke generic mail */
    public function notice(?string $email, ?string $name, string $subject, string $title, array $lines = [], ?array $button = null, string $tone = 'info', ?string $badge = null): void
    {
        $this->toPerson($email, $name, $subject, [
            'tone'   => $tone,
            'badge'  => $badge,
            'title'  => $title,
            'lines'  => $lines,
            'button' => $button,
        ]);
    }

    /* ================================================================
     |  Helpers
     ================================================================ */

    protected function link(string $routeName, mixed $param = null): string
    {
        if (! Route::has($routeName)) {
            return url('/');
        }

        return $param === null ? route($routeName) : route($routeName, $param);
    }

    protected function when(?CarbonInterface $dt = null): string
    {
        return ($dt ?? now())
            ->copy()
            ->timezone(config('easydoc.timezone', 'Asia/Dhaka'))
            ->format('d M Y, h:i A');
    }

    protected function doctorEmail(Doctor $doctor): ?string
    {
        return $doctor->user?->email ?? $doctor->getAttribute('email');
    }

    protected function doctorName(Doctor $doctor): string
    {
        return $doctor->user?->name ?? $doctor->getAttribute('name') ?? 'ডাক্তার';
    }

    protected function browserInfo(?string $agent): string
    {
        $agent = (string) $agent;

        $browser = match (true) {
            str_contains($agent, 'Edg/')    => 'Edge',
            str_contains($agent, 'OPR/')    => 'Opera',
            str_contains($agent, 'Chrome/') => 'Chrome',
            str_contains($agent, 'Firefox/') => 'Firefox',
            str_contains($agent, 'Safari/') => 'Safari',
            default                         => 'Unknown browser',
        };

        $os = match (true) {
            str_contains($agent, 'Windows')                                 => 'Windows',
            str_contains($agent, 'Android')                                 => 'Android',
            str_contains($agent, 'iPhone') || str_contains($agent, 'iPad')  => 'iOS',
            str_contains($agent, 'Mac OS')                                  => 'macOS',
            str_contains($agent, 'Linux')                                   => 'Linux',
            default                                                         => 'Unknown OS',
        };

        return "$browser, $os";
    }

    /* ================================================================
     |  Registration
     ================================================================ */

    public function doctorRegistered(Doctor $doctor, bool $createdByAdmin = false): void
    {
        $name  = $this->doctorName($doctor);
        $email = $this->doctorEmail($doctor);

        // Doctor ke
        if ($createdByAdmin) {
            $this->toPerson($email, $name, 'EasyDoc: আপনার অ্যাকাউন্ট তৈরি হয়েছে', [
                'tone'    => 'success',
                'badge'   => 'অ্যাকাউন্ট তৈরি',
                'title'   => 'আপনার EasyDoc অ্যাকাউন্ট তৈরি হয়েছে',
                'lines'   => ['Super Admin আপনার জন্য একটি অ্যাকাউন্ট তৈরি করেছেন। নিচের বাটন থেকে লগইন করুন।'],
                'details' => ['নাম' => $name, 'ইমেইল' => $email],
                'button'  => ['text' => 'লগইন করুন', 'url' => $this->link('login')],
            ]);

            return;
        }

        $this->toPerson($email, $name, 'EasyDoc: আপনার রেজিস্ট্রেশন জমা হয়েছে', [
            'tone'    => 'info',
            'badge'   => 'অনুমোদনের অপেক্ষায়',
            'title'   => 'রেজিস্ট্রেশন সফলভাবে জমা হয়েছে',
            'lines'   => [
                'EasyDoc এ রেজিস্ট্রেশন করার জন্য ধন্যবাদ। Super Admin আপনার তথ্য যাচাই করে অনুমোদন করলে আপনাকে ইমেইলে জানানো হবে।',
                'অনুমোদনের আগে আপনি প্রেসক্রিপশন লিখতে পারবেন না।',
            ],
            'details' => ['নাম' => $name, 'ইমেইল' => $email, 'স্ট্যাটাস' => 'অনুমোদনের অপেক্ষায়', 'সময়' => $this->when($doctor->created_at)],
        ]);

        // Admin ke
        $this->toAdmins('New doctor registration: ' . $name, [
            'tone'    => 'warning',
            'badge'   => 'Pending approval',
            'title'   => 'A new doctor has registered',
            'lines'   => ['A new doctor is waiting for your approval.'],
            'details' => ['Name' => $name, 'Email' => $email, 'Registered' => $this->when($doctor->created_at)],
            'button'  => ['text' => 'Review doctor', 'url' => $this->link('admin.doctors.show', $doctor)],
        ]);
    }

    public function doctorApprovalChanged(Doctor $doctor): void
    {
        $name   = $this->doctorName($doctor);
        $email  = $this->doctorEmail($doctor);
        $status = strtolower((string) $doctor->approval_status);

        if ($status === 'approved') {
            $this->toPerson($email, $name, 'EasyDoc: আপনার অ্যাকাউন্ট অনুমোদিত হয়েছে', [
                'tone'    => 'success',
                'badge'   => 'অনুমোদিত',
                'title'   => 'অভিনন্দন! আপনার অ্যাকাউন্ট অনুমোদিত হয়েছে',
                'lines'   => ['এখন আপনি লগইন করে চেম্বার চালু করতে এবং প্রেসক্রিপশন লিখতে পারবেন।'],
                'details' => ['নাম' => $name, 'স্ট্যাটাস' => 'অনুমোদিত', 'সময়' => $this->when()],
                'button'  => ['text' => 'লগইন করুন', 'url' => $this->link('login')],
            ]);
        } elseif ($status === 'rejected') {
            $reason = $doctor->getAttribute('rejection_reason') ?? $doctor->getAttribute('reject_reason');

            $this->toPerson($email, $name, 'EasyDoc: আপনার রেজিস্ট্রেশন অনুমোদন করা যায়নি', [
                'tone'    => 'danger',
                'badge'   => 'বাতিল',
                'title'   => 'আপনার রেজিস্ট্রেশন অনুমোদন করা যায়নি',
                'lines'   => ['দুঃখিত, Super Admin এই মুহূর্তে আপনার রেজিস্ট্রেশন অনুমোদন করেননি। প্রয়োজনে সঠিক তথ্য দিয়ে আবার চেষ্টা করুন বা সাপোর্টে যোগাযোগ করুন।'],
                'details' => ['নাম' => $name, 'স্ট্যাটাস' => 'বাতিল', 'কারণ' => $reason],
            ]);
        }
    }

    /* ================================================================
     |  Login
     ================================================================ */

    public function loginAlert(object $user, ?string $ip, ?string $agent): void
    {
        $this->toPerson($user->email ?? null, $user->name ?? null, 'EasyDoc: নতুন লগইন', [
            'tone'     => 'info',
            'badge'    => 'লগইন',
            'title'    => 'আপনার অ্যাকাউন্টে লগইন করা হয়েছে',
            'lines'    => ['আপনার EasyDoc অ্যাকাউন্টে নিচের তথ্য অনুযায়ী লগইন করা হয়েছে।'],
            'details'  => ['সময়' => $this->when(), 'IP' => $ip, 'ডিভাইস' => $this->browserInfo($agent)],
            'footnote' => 'এটা আপনি না হলে এখনই পাসওয়ার্ড পরিবর্তন করুন এবং Super Admin কে জানান।',
        ]);
    }

    /* ================================================================
     |  Chamber
     ================================================================ */

    protected function chamberDetails(Chamber $chamber): array
    {
        return [
            'চেম্বার'      => $chamber->chamber_name,
            'হাসপাতাল'    => $chamber->hospital_name,
            'ফোন'         => $chamber->phone,
            'ঠিকানা'       => $chamber->address,
            'বসার দিন'     => $chamber->visiting_days,
            'বসার সময়'    => $chamber->visiting_time,
        ];
    }

    public function chamberRequested(Chamber $chamber): void
    {
        $doctor = $chamber->doctor;
        if (! $doctor) {
            return;
        }

        $name = $this->doctorName($doctor);
        $fee  = (int) $chamber->fee_charged;

        // Doctor ke confirmation
        $this->toPerson($this->doctorEmail($doctor), $name, 'EasyDoc: চেম্বার রিকোয়েস্ট জমা হয়েছে', [
            'tone'    => 'info',
            'badge'   => 'অপেক্ষমান',
            'title'   => 'আপনার চেম্বার রিকোয়েস্ট জমা হয়েছে',
            'lines'   => array_filter([
                'Super Admin আপনার প্রেসক্রিপশন প্যাড দেখে টেমপ্লেট সেট করে চেম্বার Active করবেন।',
                $fee > 0 ? "রিকোয়েস্ট ফি ৳{$fee} আপনার ওয়ালেট থেকে কাটা হয়েছে। রিকোয়েস্ট বাতিল হলে এটি ফেরত দেওয়া হবে।" : null,
            ]),
            'details' => $this->chamberDetails($chamber),
            'button'  => ['text' => 'আমার চেম্বার দেখুন', 'url' => $this->link('doctor.chambers.index')],
        ]);

        // Admin ke
        $this->toAdmins('New chamber request from Dr. ' . $name, [
            'tone'    => 'warning',
            'badge'   => 'Review needed',
            'title'   => 'New chamber request',
            'lines'   => ["Dr. {$name} has requested a new chamber. Review the prescription pad, set a template and activate it."],
            'details' => ['Doctor' => $name] + [
                'Chamber'  => $chamber->chamber_name,
                'Hospital' => $chamber->hospital_name,
                'Phone'    => $chamber->phone,
                'Address'  => $chamber->address,
                'Days'     => $chamber->visiting_days,
                'Time'     => $chamber->visiting_time,
                'Fee paid' => $fee > 0 ? "৳{$fee}" : null,
            ],
            'button'  => ['text' => 'Review request', 'url' => $this->link('admin.chamber-requests.show', $chamber)],
        ]);
    }

    /** Admin directly chamber add korle (request chara) */
    public function chamberAdded(Chamber $chamber): void
    {
        $doctor = $chamber->doctor;
        if (! $doctor) {
            return;
        }

        $this->toPerson($this->doctorEmail($doctor), $this->doctorName($doctor), 'EasyDoc: নতুন চেম্বার যোগ করা হয়েছে', [
            'tone'    => $chamber->status ? 'success' : 'info',
            'badge'   => $chamber->status ? 'Active' : 'Inactive',
            'title'   => 'আপনার জন্য নতুন চেম্বার যোগ করা হয়েছে',
            'lines'   => ['"আমার চেম্বার" পেজ থেকে চেম্বারটি চালু করে প্রেসক্রিপশন লিখতে পারবেন।'],
            'details' => $this->chamberDetails($chamber),
            'button'  => ['text' => 'আমার চেম্বার দেখুন', 'url' => $this->link('doctor.chambers.index')],
        ]);
    }

    public function chamberApproved(Chamber $chamber): void
    {
        $doctor = $chamber->doctor;
        if (! $doctor) {
            return;
        }

        $this->toPerson($this->doctorEmail($doctor), $this->doctorName($doctor), 'EasyDoc: আপনার চেম্বার অনুমোদিত হয়েছে', [
            'tone'    => 'success',
            'badge'   => 'Active',
            'title'   => 'আপনার চেম্বার Active করা হয়েছে',
            'lines'   => ['Super Admin আপনার চেম্বারের প্রেসক্রিপশন টেমপ্লেট সেট করে চেম্বারটি Active করেছেন। "আমার চেম্বার" পেজ থেকে সুইচ চালু করলেই প্রেসক্রিপশন লিখতে পারবেন।'],
            'details' => $this->chamberDetails($chamber) + ['টেমপ্লেট' => $chamber->template?->name],
            'button'  => ['text' => 'চেম্বার চালু করুন', 'url' => $this->link('doctor.chambers.index')],
        ]);
    }

    public function chamberRejected(Chamber $chamber): void
    {
        $doctor = $chamber->doctor;
        if (! $doctor) {
            return;
        }

        $this->toPerson($this->doctorEmail($doctor), $this->doctorName($doctor), 'EasyDoc: চেম্বার রিকোয়েস্ট বাতিল হয়েছে', [
            'tone'    => 'danger',
            'badge'   => 'বাতিল',
            'title'   => 'আপনার চেম্বার রিকোয়েস্ট বাতিল হয়েছে',
            'lines'   => ['রিকোয়েস্ট ফি আপনার ওয়ালেটে ফেরত দেওয়া হয়েছে। প্রয়োজনে তথ্য ঠিক করে নতুন রিকোয়েস্ট পাঠাতে পারেন।'],
            'details' => ['চেম্বার' => $chamber->chamber_name, 'কারণ' => $chamber->reject_reason],
            'button'  => ['text' => 'আবার রিকোয়েস্ট করুন', 'url' => $this->link('doctor.chambers.request.create')],
        ]);
    }

    public function chamberStatusChanged(Chamber $chamber): void
    {
        $doctor = $chamber->doctor;
        if (! $doctor) {
            return;
        }

        $on = (bool) $chamber->status;

        $this->toPerson($this->doctorEmail($doctor), $this->doctorName($doctor), 'EasyDoc: চেম্বার ' . ($on ? 'Active' : 'Inactive') . ' করা হয়েছে', [
            'tone'    => $on ? 'success' : 'warning',
            'badge'   => $on ? 'Active' : 'Inactive',
            'title'   => $on ? 'আপনার চেম্বার Active করা হয়েছে' : 'আপনার চেম্বার Inactive করা হয়েছে',
            'lines'   => [$on
                ? 'এখন আপনি এই চেম্বার চালু করে প্রেসক্রিপশন লিখতে পারবেন।'
                : 'Super Admin এই চেম্বার Inactive করেছেন, তাই এটি দিয়ে প্রেসক্রিপশন লেখা যাবে না। বিস্তারিত জানতে Super Admin এর সাথে যোগাযোগ করুন।'],
            'details' => ['চেম্বার' => $chamber->chamber_name, 'হাসপাতাল' => $chamber->hospital_name],
            'button'  => ['text' => 'আমার চেম্বার দেখুন', 'url' => $this->link('doctor.chambers.index')],
        ]);
    }
}
