<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Artisan::command('chatbot:build-faq-index {--path=chatbot/faq.json} {--out=chatbot/faq_index.json}', function () {
//     $this->comment('Generating the vector');
// });
