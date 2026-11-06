<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Services\MailNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupportChatController extends Controller
{
    public function __construct(private MailNotifier $mailNotifier) {}

    private function doctor(): Doctor
    {
        $doctor = auth()->user()?->doctor;
        abort_unless($doctor, 403);

        return $doctor;
    }

    public function state(Request $request): JsonResponse
    {
        $doctor = $this->doctor();
        $conversation = SupportConversation::where('doctor_id', $doctor->id)
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->first();

        if (! $conversation) {
            return response()->json(['conversation' => null, 'unread' => 0, 'messages' => []]);
        }

        $unread = $conversation->messages()
            ->where('sender_type', 'admin')
            ->whereNull('read_at')
            ->count();

        if ($request->boolean('peek')) {
            return response()->json([
                'conversation' => $this->conversationPayload($conversation),
                'unread' => $unread,
                'messages' => [],
            ]);
        }

        $afterId = max(0, (int) $request->query('after_id', 0));
        $query = $conversation->messages()->with('sender:id,name')->orderBy('id');
        $messages = $afterId > 0
            ? $query->where('id', '>', $afterId)->get()
            : $conversation->messages()->with('sender:id,name')->orderByDesc('id')->limit(100)->get()->reverse()->values();

        $readQuery = $conversation->messages()->where('sender_type', 'admin')->whereNull('read_at');
        if ($afterId > 0) {
            $readQuery->whereIn('id', $messages->pluck('id'));
        }
        $readQuery->update(['read_at' => now()]);

        return response()->json([
            'conversation' => $this->conversationPayload($conversation->fresh()),
            'unread' => $conversation->messages()->where('sender_type', 'admin')->whereNull('read_at')->count(),
            'messages' => $messages->map(fn (SupportMessage $message) => $this->messagePayload($message))->values(),
        ]);
    }

    public function send(Request $request): JsonResponse
    {
        $request->merge(['body' => trim((string) $request->input('body', ''))]);
        $data = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:2000'],
        ]);

        $doctor = $this->doctor();
        $user = auth()->user();

        [$conversation, $message] = DB::transaction(function () use ($doctor, $user, $data) {
            // Serialize first-message requests for one doctor to avoid parallel duplicate open threads.
            Doctor::whereKey($doctor->id)->lockForUpdate()->firstOrFail();

            $conversation = SupportConversation::where('doctor_id', $doctor->id)
                ->where('status', 'open')
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            if (! $conversation) {
                $conversation = SupportConversation::create([
                    'doctor_id' => $doctor->id,
                    'subject' => Str::limit(trim(preg_replace('/\s+/', ' ', $data['body'])), 120),
                    'status' => 'open',
                    'last_message_at' => now(),
                ]);
            }

            $message = $conversation->messages()->create([
                'sender_id' => $user->id,
                'sender_type' => 'doctor',
                'body' => trim($data['body']),
            ]);

            $conversation->update(['last_message_at' => $message->created_at]);

            return [$conversation, $message->load('sender:id,name')];
        });

        $doctorName = $doctor->displayName();
        $snippet = Str::limit(trim(preg_replace('/\s+/', ' ', $message->body)), 180);
        $this->mailNotifier->toAdmins('New EasyDoc support message from ' . $doctorName, [
            'tone' => 'info',
            'badge' => 'Doctor support',
            'title' => 'New message from ' . $doctorName,
            'lines' => [$snippet],
            'details' => [
                'Doctor' => $doctorName,
                'Email' => $doctor->user?->email,
            ],
            'button' => [
                'text' => 'Reply in live chat',
                'url' => route('admin.support.show', $conversation),
            ],
        ]);

        return response()->json([
            'conversation' => $this->conversationPayload($conversation->fresh()),
            'message' => $this->messagePayload($message),
        ], 201);
    }

    private function conversationPayload(SupportConversation $conversation): array
    {
        return [
            'id' => $conversation->id,
            'subject' => $conversation->subject,
            'status' => $conversation->status,
            'last_message_at' => $conversation->last_message_at?->toIso8601String(),
        ];
    }

    private function messagePayload(SupportMessage $message): array
    {
        return [
            'id' => $message->id,
            'sender_type' => $message->sender_type,
            'sender_name' => $message->sender_type === 'admin' ? 'EasyDoc Support' : ($message->sender?->name ?? 'Doctor'),
            'body' => $message->body,
            'time' => $message->created_at?->timezone(config('easydoc.timezone', 'Asia/Dhaka'))->format('d M, h:i A'),
        ];
    }
}
