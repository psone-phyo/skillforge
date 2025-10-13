<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class Gemini
{
    protected string $apiKey;
    protected string $genModel;
    protected string $embedModel;

    public function __construct(?string $apiKey = null, ?string $genModel = null, ?string $embedModel = null)
    {
        $this->apiKey     = $apiKey     ?? config('services.gemini.key');
        $this->genModel   = $genModel   ?? config('services.gemini.gen_model', 'models/gemini-2.0-flash-lite-latest');
        $this->embedModel = $embedModel ?? config('services.gemini.embed_model', 'models/text-embedding-004');
    }

    public function embed(string $text): array
    {
        $resp = Http::withHeaders([
            'Content-Type'   => 'application/json',
            'x-goog-api-key' => $this->apiKey,
        ])->post('https://generativelanguage.googleapis.com/v1/models/text-embedding-004:embedContent', [
            'model'   => $this->embedModel,
            'content' => [
                'parts' => [
                    ['text' => $text],
                ],
            ],
        ]);

        if (!$resp->ok()) {
            throw new \RuntimeException('Gemini embed failed: ' . $resp->body());
        }

        $json = $resp->json();
        return $json['embedding']['values'] ?? [];
    }

    // Simple single-message generation (kept for convenience)
    public function generate(string $system, string $userPrompt, ?string $model = null): string
    {
        $model = $model ?: $this->genModel;
        $combined = trim($system) . "\n\nUser: " . $userPrompt;

        $payload = [
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => [['text' => $combined]],
                ],
            ],
        ];

        $resp = Http::withHeaders([
            'Content-Type'   => 'application/json',
            'x-goog-api-key' => $this->apiKey,
        ])->post("https://generativelanguage.googleapis.com/v1/{$model}:generateContent", $payload);

        if (!$resp->ok()) {
            throw new \RuntimeException('Gemini generation failed: ' . $resp->body());
        }

        $json = $resp->json();
        return $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    // Multi-turn chat support: pass recent turns and extra context
    public function generateChat(array $messages, ?string $model = null): string
    {
        // $messages is an array of ['role' => 'user'|'model', 'text' => '...']
        $model = $model ?: $this->genModel;

        $contents = [];
        foreach ($messages as $m) {
            $role = $m['role'] ?? 'user'; // 'user' or 'model'
            $text = $m['text'] ?? '';
            if ($text === '') continue;
            $contents[] = [
                'role'  => $role,
                'parts' => [['text' => $text]],
            ];
        }

        $resp = Http::withHeaders([
            'Content-Type'   => 'application/json',
            'x-goog-api-key' => $this->apiKey,
        ])->post("https://generativelanguage.googleapis.com/v1/{$model}:generateContent", [
            'contents' => $contents,
        ]);

        if (!$resp->ok()) {
            throw new \RuntimeException('Gemini generation failed: ' . $resp->body());
        }

        $json = $resp->json();
        return $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    public static function cosine(array $a, array $b): float
    {
        $dot = 0.0; $na = 0.0; $nb = 0.0;
        $len = min(count($a), count($b));
        for ($i = 0; $i < $len; $i++) {
            $dot += $a[$i] * $b[$i];
            $na  += $a[$i] * $a[$i];
            $nb  += $b[$i] * $b[$i];
        }
        if ($na == 0.0 || $nb == 0.0) return 0.0;
        return $dot / (sqrt($na) * sqrt($nb));
    }
}
