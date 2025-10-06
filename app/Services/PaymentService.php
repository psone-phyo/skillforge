<?php

namespace App\Services;

use App\Models\Payment;

class PaymentService{
        function generatePaymentRef(): string
{
    $lastRef = Payment::latest('id')->value('ref') ?? 'AAAAAA';

    $chars = str_split($lastRef);
    $i = count($chars) - 1;

    while ($i >= 0) {
        if ($chars[$i] === 'Z') {
            $chars[$i] = 'A';
            $i--;
        } else {
            $chars[$i] = chr(ord($chars[$i]) + 1);
            break;
        }
    }

    if ($i < 0) {
        array_unshift($chars, 'A');
    }

    return implode('', $chars);
}
}
