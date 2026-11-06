<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

    public function show(SupportConversation $conversation)
    {
        $conversation->load('doctor.user');
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
            'messages' => $messages->map(fn (SupportMessage $message) => $this->messagePayload($message))->values(),
        ]);
    }

    public function reply(Request $request, SupportConversation $conversation): JsonResponse
    {
        abort_unless($conversation->status === 'open', 409, 'This conversation is closed.');
        $request->merge(['body' => trim((string) $request->input('body', ''))]);
        $data = $request->validate(['body' => ['required', 'string', 'min:1', 'max:2000']]);

        $message = $conversation->messages()->create([
            'sender_id' => auth()->id(),
            'sender_type' => 'admin',
            'body' => trim($data['body']),
        ]);
        $conversation->update(['last_message_at' => $message->created_at]);

        return response()->json(['message' => $this->messagePayload($message->load('sender:id,name'))], 201);
    }

    public function close(SupportConversation $conversation): RedirectResponse
    {
        $conversation->update(['status' => 'closed']);

        return redirect()->route('admin.support.show', $conversation)->with('success', 'Support conversation closed.');
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
