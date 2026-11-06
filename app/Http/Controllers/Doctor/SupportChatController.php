<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Services\MailNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
            return response()->json([
                'conversation' => null,
                'unread' => 0,
                'admin_online' => false,
                'admin_typing' => false,
                'messages' => [],
            ]);
        }

        $unread = $conversation->messages()
            ->where('sender_type', 'admin')
            ->whereNull('read_at')
            ->count();
        $presence = [
            'admin_online' => Cache::has($this->cacheKey($conversation, 'admin-active')),
            'admin_typing' => Cache::has($this->cacheKey($conversation, 'admin-typing')),
        ];

        if ($request->boolean('peek')) {
            return response()->json([
                'conversation' => $this->conversationPayload($conversation),
                'unread' => $unread,
                ...$presence,
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
            ...$presence,
            'messages' => $messages->map(fn (SupportMessage $message) => $this->messagePayload($message))->values(),
        ]);
    }

    public function send(Request $request): JsonResponse
    {
        $request->merge(['body' => trim((string) $request->input('body', ''))]);
        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ]);
        $this->ensureMessageContent($data['body'] ?? null, $request);

        $doctor = $this->doctor();
        $user = auth()->user();

        [$conversation, $message] = DB::transaction(function () use ($doctor, $user, $data, $request) {
            Doctor::whereKey($doctor->id)->lockForUpdate()->firstOrFail();

            $conversation = SupportConversation::where('doctor_id', $doctor->id)
                ->where('status', 'open')
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            if (! $conversation) {
                $conversation = SupportConversation::create([
                    'doctor_id' => $doctor->id,
                    'subject' => Str::limit(($data['body'] ?? '') ?: 'Image attachment', 120),
                    'status' => 'open',
                    'last_message_at' => now(),
                ]);
            }

            $message = $conversation->messages()->create([
                'sender_id' => $user->id,
                'sender_type' => 'doctor',
                'body' => (string) ($data['body'] ?? ''),
            ]);

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $path = $file->store('support-chat/' . $conversation->id, 'local');
                $message->update([
                    'image_path' => $path,
                    'image_name' => $file->getClientOriginalName(),
                ]);
                $message->refresh();
            }

            $conversation->update(['last_message_at' => $message->created_at]);

            return [$conversation, $message->load('sender:id,name')];
        });

        $doctorName = $doctor->displayName();
        $snippet = trim(preg_replace('/\s+/', ' ', $message->body));
        if ($message->image_path) {
            $snippet = trim($snippet . ($snippet !== '' ? ' · ' : '') . 'Image attached');
        }
        $this->mailNotifier->toAdmins('New EasyDoc support message from ' . $doctorName, [
            'tone' => 'info',
            'badge' => 'Doctor support',
            'title' => 'New message from ' . $doctorName,
            'lines' => [Str::limit($snippet, 180)],
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

    public function typing(Request $request): JsonResponse
    {
        $doctor = $this->doctor();
        $conversationId = $request->integer('conversation_id');
        $conversation = $conversationId
            ? SupportConversation::where('doctor_id', $doctor->id)->whereKey($conversationId)->first()
            : SupportConversation::where('doctor_id', $doctor->id)->where('status', 'open')->orderByDesc('id')->first();

        if ($conversation && $conversation->status === 'open') {
            Cache::put($this->cacheKey($conversation, 'doctor-typing'), true, now()->addSeconds(7));
        }

        return response()->json(['ok' => true]);
    }

    public function image(SupportMessage $message)
    {
        abort_unless($message->conversation()->where('doctor_id', $this->doctor()->id)->exists(), 404);

        return $this->privateImageResponse($message);
    }

    private function ensureMessageContent(?string $body, Request $request): void
    {
        if (blank($body) && ! $request->hasFile('image')) {
            throw ValidationException::withMessages(['body' => 'Write a message or attach an image.']);
        }
    }

    private function privateImageResponse(SupportMessage $message)
    {
        abort_unless($message->image_path && Storage::disk('local')->exists($message->image_path), 404);
        $mime = Storage::disk('local')->mimeType($message->image_path) ?: 'application/octet-stream';
        $name = Str::replace(['"', "\r", "\n"], '', $message->image_name ?: 'support-image');

        return Storage::disk('local')->response($message->image_path, $name, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }

    private function cacheKey(SupportConversation $conversation, string $suffix): string
    {
        return 'support-chat.' . $conversation->id . '.' . $suffix;
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
            'image_url' => $message->image_path ? route('doctor.support-chat.image', $message) : null,
            'image_name' => $message->image_name,
            'time' => $message->created_at?->timezone(config('easydoc.timezone', 'Asia/Dhaka'))->format('d M, h:i A'),
        ];
    }
}
