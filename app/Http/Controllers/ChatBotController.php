<?php

namespace App\Http\Controllers;

use App\Services\Gemini;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChatBotController extends Controller
{
    protected string $historyPath;
    protected string $indexPath   = 'chatbot/faq_index.json';

    public function __construct(){
        $this->historyPath = 'chatbot/history_user_'.Auth::id().'.json';
    }
    // pull last N pairs for context
    protected function recentTurns(array $items, int $pairs = 4): array
    {
        // items: [{question, answer, ...}]
        $slice = array_slice($items, -$pairs);
        foreach ($slice as $it) {
            $turns[] = ['role' => 'user',  'text' => (string)($it['question'] ?? '')];
            $turns[] = ['role' => 'model', 'text' => (string)($it['answer']   ?? '')];
        }
        return $turns;
    }

    protected function isGreeting(string $text): bool
    {
        $t = mb_strtolower(trim($text));
        return (bool)preg_match('/^(hi|hello|hey|mingalaba|hola|sup|yo|hi there|hello there)\b/u', $t);
    }

    public function history()
    {
        if (!Storage::exists($this->historyPath)) {
            return response()->json(['items' => []]);
        }
        $json = json_decode(Storage::get($this->historyPath), true);
        return response()->json(['items' => $json ?: []]);
    }

    public function message(Request $request, Gemini $gemini)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
        ]);
        $userPrompt = trim($data['message']);

        // Load history (global file; you can switch to per-user later)
        $history = [];
        if (Storage::exists($this->historyPath)) {
            $history = json_decode(Storage::get($this->historyPath), true) ?: [];
        }

        // Friendly greeting path
        if ($this->isGreeting($userPrompt)) {
            $answer = "Hi! I’m the SkillForge Assistant. I can answer FAQs (payments, access, courses) and help you navigate the platform. What can I help you with?";
            $entry = [
                'id'        => uniqid('msg_', true),
                'timestamp' => now()->toIso8601String(),
                'question'  => $userPrompt,
                'answer'    => $answer,
            ];
            $history[] = $entry;
            Storage::put($this->historyPath, json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return response()->json(['reply' => $answer, 'entry' => $entry]);
        }

        // Load embedding index
        $index = [];
        if (Storage::exists($this->indexPath)) {
            $index = json_decode(Storage::get($this->indexPath), true) ?: [];
        }

        // If no index yet, fall back to general model with conversation context
        if (empty($index)) {
            $messages = [
                ['role' => 'user', 'text' => "You are a helpful assistant for SkillForge LMS. Be concise and helpful."],
                ...$this->recentTurns($history, 4),
                ['role' => 'user', 'text' => $userPrompt],
            ];
            $answer = $gemini->generateChat($messages);

            $entry = [
                'id'        => uniqid('msg_', true),
                'timestamp' => now()->toIso8601String(),
                'question'  => $userPrompt,
                'answer'    => $answer ?: "I'm here to help. Could you rephrase that?",
            ];
            $history[] = $entry;
            Storage::put($this->historyPath, json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return response()->json(['reply' => $entry['answer'], 'entry' => $entry]);
        }

        // Embed query and rank by cosine
        $qVec = $gemini->embed($userPrompt);
        $scored = [];
        foreach ($index as $row) {
            $score = \App\Services\Gemini::cosine($qVec, $row['vector'] ?? []);
            $scored[] = ['q' => $row['q'], 'a' => $row['a'], 'score' => $score];
        }
        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        $best = $scored[0]['score'] ?? 0.0;
        $topK = array_slice($scored, 0, 6);

        // thresholds (tune as you like)
        $HIGH = 0.35;  // confident FAQ match -> direct answer
        $LOW  = 0.15;  // weak match -> call model with context

        // Strong FAQ hit → answer directly (feels snappy)
        if ($best >= $HIGH) {
            $answer = $topK[0]['a'];
            $entry = [
                'id'        => uniqid('msg_', true),
                'timestamp' => now()->toIso8601String(),
                'question'  => $userPrompt,
                'answer'    => $answer,
            ];
            $history[] = $entry;
            Storage::put($this->historyPath, json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return response()->json(['reply' => $answer, 'entry' => $entry]);
        }

        // Build compact FAQ context from topK
        $ctx = '';
        $used = 0;
        foreach ($topK as $i => $item) {
            $chunk = "Q: {$item['q']}\nA: {$item['a']}\n\n";
            if ($used + strlen($chunk) > 6000) break;
            $ctx .= $chunk;
            $used += strlen($chunk);
        }

        // Mixed mode (weak/medium) → prefer FAQ if available, but allow helpful completion
        if ($best >= $LOW) {
            $messages = [
                ['role' => 'user', 'text' => "You are a helpful assistant for SkillForge LMS. Prefer to answer using this FAQ context if relevant, but you may also help beyond it when needed.\n\nFAQ Context:\n{$ctx}"],
                ...$this->recentTurns($history, 4),
                ['role' => 'user', 'text' => $userPrompt],
            ];

            $answer = $gemini->generateChat($messages);
            if (!$answer) {
                // fallback to best FAQ if model returns nothing
                $answer = $topK[0]['a'] ?? "I'm not sure yet. Could you tell me more?";
            }

            $entry = [
                'id'        => uniqid('msg_', true),
                'timestamp' => now()->toIso8601String(),
                'question'  => $userPrompt,
                'answer'    => $answer,
            ];
            $history[] = $entry;
            Storage::put($this->historyPath, json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return response()->json(['reply' => $answer, 'entry' => $entry]);
        }

        // Very weak/no match → general helpful assistant with conversation context
        $messages = [
            ['role' => 'user', 'text' => "You are a helpful assistant for SkillForge LMS. Be concise and helpful. If the user asks about platform features or account help, guide them with steps."],
            ...$this->recentTurns($history, 4),
            ['role' => 'user', 'text' => $userPrompt],
        ];
        $answer = $gemini->generateChat($messages);
        if (!$answer) {
            $answer = "I'm here to help. Could you clarify what you need?";
        }

        $entry = [
            'id'        => uniqid('msg_', true),
            'timestamp' => now()->toIso8601String(),
            'question'  => $userPrompt,
            'answer'    => $answer,
        ];
        $history[] = $entry;
        Storage::put($this->historyPath, json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return response()->json(['reply' => $answer, 'entry' => $entry]);
    }
}
