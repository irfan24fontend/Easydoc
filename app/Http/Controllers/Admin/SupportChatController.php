<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SupportChatController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'open');
        abort_unless(in_array($status, ['open', 'closed', 'all'], true), 404);

        $conversations = SupportConversation::with(['doctor.user', 'latestMessage'])
            ->withCount(['messages as unread_count' => fn ($query) => $query
                ->where('sender_type', 'doctor')->whereNull('read_at')])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->orderByDesc('last_message_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.support.index', compact('conversations', 'status'));
    }

    public function create()
    {
        $doctors = Doctor::with('user')->orderBy('id')->get();

        return view('admin.support.create', compact('doctors'));
    }

    public function start(Request $request): RedirectResponse
    {
        $request->merge(['body' => trim((string) $request->input('body', ''))]);
        $data = $request->validate([
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'body' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ]);
        $this->ensureMessageContent($data['body'] ?? null, $request);

        $doctor = Doctor::with('user')->findOrFail($data['doctor_id']);
        $conversation = SupportConversation::where('doctor_id', $doctor->id)
            ->where('status', 'open')
            ->orderByDesc('id')
            ->first();

        if (! $conversation) {
            $conversation = SupportConversation::create([
                'doctor_id' => $doctor->id,
                'subject' => Str::limit($data['body'] ?: 'Image from EasyDoc Support', 120),
                'status' => 'open',
                'last_message_at' => now(),
            ]);
        }

        $message = $this->createMessage($request, $conversation, (string) ($data['body'] ?? ''), 'admin');
        $conversation->update(['last_message_at' => $message->created_at]);

        return redirect()->route('admin.support.show', $conversation)->with('success', 'Message sent to ' . $doctor->displayName() . '.');
    }

    public function show(SupportConversation $conversation)
    {
        $conversation->load('doctor.user');
        Cache::put($this->cacheKey($conversation, 'admin-active'), true, now()->addSeconds(15));
        $messages = $conversation->messages()
            ->with('sender:id,name')
            ->orderByDesc('id')
            ->limit(100)
            ->get()
            ->reverse()
            ->values();

        $conversation->messages()
            ->where('sender_type', 'doctor')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('admin.support.show', compact('conversation', 'messages'));
    }

    public function messages(Request $request, SupportConversation $conversation): JsonResponse
    {
        Cache::put($this->cacheKey($conversation, 'admin-active'), true, now()->addSeconds(15));
        $afterId = max(0, (int) $request->query('after_id', 0));
        $messages = $conversation->messages()
            ->with('sender:id,name')
            ->when($afterId > 0, fn ($query) => $query->where('id', '>', $afterId))
            ->orderBy('id')
            ->when($afterId === 0, fn ($query) => $query->limit(100))
            ->get();

        $readQuery = $conversation->messages()->where('sender_type', 'doctor')->whereNull('read_at');
        if ($afterId > 0) {
            $readQuery->whereIn('id', $messages->pluck('id'));
        }
        $readQuery->update(['read_at' => now()]);

        return response()->json([
            'status' => $conversation->status,
            'doctor_typing' => Cache::has($this->cacheKey($conversation, 'doctor-typing')),
            'messages' => $messages->map(fn (SupportMessage $message) => $this->messagePayload($message))->values(),
        ]);
    }

    public function reply(Request $request, SupportConversation $conversation): JsonResponse
    {
        abort_unless($conversation->status === 'open', 409, 'This conversation is closed.');
        $request->merge(['body' => trim((string) $request->input('body', ''))]);
        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ]);
        $this->ensureMessageContent($data['body'] ?? null, $request);

        $message = $this->createMessage($request, $conversation, (string) ($data['body'] ?? ''), 'admin');
        $conversation->update(['last_message_at' => $message->created_at]);

        return response()->json(['message' => $this->messagePayload($message->load('sender:id,name'))], 201);
    }

    public function typing(SupportConversation $conversation): JsonResponse
    {
        if ($conversation->status === 'open') {
            Cache::put($this->cacheKey($conversation, 'admin-typing'), true, now()->addSeconds(7));
            Cache::put($this->cacheKey($conversation, 'admin-active'), true, now()->addSeconds(15));
        }

        return response()->json(['ok' => true]);
    }

    public function image(SupportMessage $message)
    {
        return $this->privateImageResponse($message);
    }

    public function close(SupportConversation $conversation): RedirectResponse
    {
        $conversation->update(['status' => 'closed']);

        return redirect()->route('admin.support.show', $conversation)->with('success', 'Support conversation closed.');
    }

    private function createMessage(Request $request, SupportConversation $conversation, string $body, string $senderType): SupportMessage
    {
        $message = $conversation->messages()->create([
            'sender_id' => auth()->id(),
            'sender_type' => $senderType,
            'body' => $body,
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

        return $message;
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

    private function messagePayload(SupportMessage $message): array
    {
        return [
            'id' => $message->id,
            'sender_type' => $message->sender_type,
            'sender_name' => $message->sender_type === 'admin' ? 'EasyDoc Support' : ($message->sender?->name ?? 'Doctor'),
            'body' => $message->body,
            'image_url' => $message->image_path ? route('admin.support.image', $message) : null,
            'image_name' => $message->image_name,
            'time' => $message->created_at?->timezone(config('easydoc.timezone', 'Asia/Dhaka'))->format('d M, h:i A'),
        ];
    }
}
