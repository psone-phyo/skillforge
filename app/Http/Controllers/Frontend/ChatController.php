<?php

namespace App\Http\Controllers\Frontend;

use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ChatController extends Controller
{
    public function index(){
        $me = Auth::user();
        return Inertia::render('ChatPage', compact('me'));
    }

        // GET /conversations
    public function conversations(Request $request)
    {
        $userId = Auth::id();
        $items = Conversation::forUser($userId)
            ->with(['messages' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->orderByDesc('updated_at')
            ->get()
            ->map(function ($c) use ($userId) {
                $last = $c->messages->first();
                $other = $c->otherPartyFor($userId);
                return [
                    'id' => $c->id,
                    'title' => $other?->name ?? 'Conversation',
                    'participants' => [
                        ['id' => $userId, 'name' => Auth::user()?->name, 'avatar' => Auth::user()?->profile_url],
                        ['id' => $other?->id, 'name' => $other?->name, 'avatar' => $other?->profile_url ?? null],
                    ],
                    'last_message' => $last ? ['body' => $last->message, 'created_at' => $last->created_at->toIso8601String()] : null,
                    'unread_count' => Message::where('conversation_id', $c->id)->where('sender_id', '!=', $userId)->where('is_read', 0)->count(),
                    'updated_at' => $c->updated_at?->toIso8601String(),
                ];
            });

        return response()->json($items);
    }

    // GET /conversations/{id}/messages?page=1&per_page=30
    public function messages(Request $request, $id)
    {
        $userId = Auth::id();
        $conv = Conversation::findOrFail($id);
        // authorization: ensure user is participant
        abort_unless(in_array($userId, [$conv->user_one_id, $conv->user_two_id]), 403);

        $perPage = (int)($request->get('per_page', 30));
        $page = (int)($request->get('page', 1));

        $query = Message::where('conversation_id', $conv->id)
            ->orderByDesc('created_at');

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $data = collect($paginator->items())->map(function ($m) {
            return [
                'id' => $m->id,
                'conversation_id' => $m->conversation_id,
                'sender' => [
                    'id' => $m->sender?->id,
                    'name' => $m->sender?->name,
                    'avatar' => $m->sender?->profile_url ?? null,
                ],
                'body' => $m->message,
                'created_at' => $m->created_at?->toIso8601String(),
                'read_at' => $m->is_read ? $m->updated_at?->toIso8601String() : null,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'next_page' => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
                'per_page' => $paginator->perPage(),
            ],
        ]);
    }

    // POST /messages
    public function send(Request $request)
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'conversation_id' => ['required', 'integer', 'exists:conversations,id'],
            'body' => ['required', 'string'],
        ]);

        $conv = Conversation::findOrFail($validated['conversation_id']);
        abort_unless(in_array($userId, [$conv->user_one_id, $conv->user_two_id]), 403);

        $message = Message::create([
            'message' => $validated['body'],
            'conversation_id' => $conv->id,
            'sender_id' => $userId,
            'is_read' => 0,
        ]);

        // Touch conversation updated_at for ordering
        $conv->touch();

        // Broadcast
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender' => [
                'id' => $message->sender?->id,
                'name' => $message->sender?->name,
                'avatar' => $message->sender?->profile_url ?? null,
            ],
            'body' => $message->message,
            'created_at' => $message->created_at?->toIso8601String(),
        ]);
    }

    // POST /conversations/{id}/read
    public function markRead(Request $request, $id)
    {
        $userId = Auth::id();
        $conv = Conversation::findOrFail($id);
        abort_unless(in_array($userId, [$conv->user_one_id, $conv->user_two_id]), 403);

        Message::where('conversation_id', $conv->id)
            ->where('sender_id', '!=', $userId)
            ->update(['is_read' => 1]);

        return response()->json(['status' => 'ok']);
    }

    public function typing(Request $request, $conversation)
    {
        $user = $request->user();

        broadcast(new UserTyping($user->id, $conversation))->toOthers();

        return response()->json(['status' => 'ok']);
    }

    public function makeChat($id){
        $user1 = Auth::id();
        $user2 = $id;
        $conversation = Conversation::where(function ($q) use ($user1, $user2) {
            $q->where('user_one_id', $user1)
            ->where('user_two_id', $user2);
        })
        ->orWhere(function ($q) use ($user1, $user2) {
            $q->where('user_one_id', $user2)
            ->where('user_two_id', $user1);
        })
        ->first();

        if (!$conversation){
            Conversation::create([
                'user_one_id' => $id,
                'user_two_id' => Auth::id()
            ]);
        }

        return redirect('/chat');
    }
}
