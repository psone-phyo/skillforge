<?php

namespace App\Console\Commands;

use App\Services\Gemini;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'chatbot:build-faq-index', description: 'Build embeddings index for FAQ JSON using Gemini embeddings')]
class BuildFaqIndex extends Command
{
    protected $signature = 'chatbot:build-faq-index {--path=chatbot/faq.json} {--out=chatbot/faq_index.json}';
    protected $description = 'Build embeddings index for FAQ JSON using Gemini embeddings';

    public function handle(Gemini $gemini)
    {
        $in  = $this->option('path');
        $out = $this->option('out');

        if (!Storage::exists($in)) {
            $this->error("FAQ file not found: storage/app/{$in}");
            return self::FAILURE;
        }

        $faq = json_decode(Storage::get($in), true);
        if (!is_array($faq)) {
            $this->error("Invalid FAQ JSON format.");
            return self::FAILURE;
        }

        $index = [];
        foreach ($faq as $i => $item) {
            $q = (string)($item['q'] ?? '');
            $a = (string)($item['a'] ?? '');
            if ($q === '' || $a === '') continue;

            $textForEmbedding = $q . "\n" . $a;
            try {
                $vec = $gemini->embed($textForEmbedding);
            } catch (\Throwable $e) {
                $this->warn("Embed failed for item #{$i}: " . $e->getMessage());
                continue;
            }

            $index[] = [
                'q'      => $q,
                'a'      => $a,
                'vector' => $vec,
            ];
            $this->info("Embedded FAQ #{$i} (" . mb_strimwidth($q, 0, 64, '…') . ")");
        }

        Storage::put($out, json_encode($index, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info("Index written to storage/app/{$out}");

        return self::SUCCESS;
    }
}
