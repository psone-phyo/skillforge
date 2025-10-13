<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ChatBotController extends Controller
{
        protected string $historyPath;
        protected string $faqPath;
        protected string $user_id;

    public function __construct()
    {
        // For per-user history, you could do: "chatbot/user_" . (auth()->id() ?: 'guest') . ".json"
        $this->user_id = Auth::id();
        $this->historyPath = asset("chatbot/history_$this->user_id.json");
                $this->faqPath = asset('chatbot/faq.json');

    }

    public function history()
    {
        if (!Storage::exists($this->historyPath)) {
            return response()->json(['items' => []]);
        }
        $json = json_decode(Storage::get($this->historyPath), true);
        return response()->json(['items' => $json ?: []]);
    }

    public function message(Request $request)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
        ]);

        $userPrompt = trim($data['message']);

        $apiKey = config('services.gemini.key', env('GEMINI_API_KEY'));
        if (!$apiKey) {
            return response()->json(['message' => 'Missing GEMINI_API_KEY'], 500);
        }

        // Minimal single-turn call to Gemini 2.0 Flash
        // API docs: https://ai.google.dev/gemini-api/docs
        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [[
                        'text' => "You are a helpful FAQ chatbot for SkillForge LMS. Keep answers concise and actionable.\n\nUser: {$userPrompt}",
                    ]],
                ],
            ],
        ];

        $resp = Http::withHeaders([
            'Content-Type' => 'application/json',
            'x-goog-api-key' => $apiKey,
        ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent', $payload);

        if (!$resp->ok()) {
            return response()->json([
                'message' => 'Chat service error',
                'detail' => $resp->json(),
            ], 502);
        }

        $body = $resp->json();
        $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? 'Sorry, I could not answer that.';

        // Append to history
        $entry = [
            'id'        => uniqid('msg_', true),
            'timestamp' => now()->toIso8601String(),
            'question'  => $userPrompt,
            'answer'    => $text,
        ];

        $items = [];
        if (Storage::exists($this->historyPath)) {
            $items = json_decode(Storage::get($this->historyPath), true) ?: [];
        }
        $items[] = $entry;

        Storage::put($this->historyPath, json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return response()->json(['reply' => $text, 'entry' => $entry]);
    }
}
