<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentController
{
    // Create a payment intent
    public function createPaymentIntent(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $session = Session::create([
            'line_items' => [[
                'price_data' => [
                    'currency' => 'mmk',
                    'product_data' => [
                        'name' => 'testing course',
                    ],
                    'unit_amount' => $request->fee * 100,
                ],
                'quantity' => 1,
            ]],
                'payment_intent_data' => [
                    'metadata' => [
                        'course_id' => $request->course_id,
                        'fee'       => $request->fee,
                    ],
                ],
            'mode' => 'payment',
            'success_url' => url('/payment-success?session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url' => url('/payment-cancel'),
        ]);
        return response()->json(['url' => $session->url]);
    }

    public function success(Request $request, PaymentService $paymentService)
    {
        $session_id = $request->query('session_id');

        if (!$session_id) {
            return redirect('/')->with('error', 'Missing session ID');
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $session = Session::retrieve($session_id);

        $paymentIntent = PaymentIntent::retrieve($session->payment_intent);
        Payment::create([
            'ref' => $paymentService->generatePaymentRef(),
            'user_id' => Auth::id(),
            'course_id' => $paymentIntent->metadata->course_id,
            'course_fee' => $paymentIntent->metadata->fee,
            'comission' => 0,
            'total_amount' => $paymentIntent->metadata->fee,
            'payment_method' => 'Card',
            'status' => 'approved'
        ]);

        return redirect("/course/".$paymentIntent->metadata->course_id)->with(['success' => 'Payment uploaded successfully']);

    }

    public function cancel(Request $request){
              $session_id = $request->query('session_id');

        if (!$session_id) {
            return redirect('/')->with('error', 'Missing session ID');
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $session = Session::retrieve($session_id);

        $paymentIntent = PaymentIntent::retrieve($session->payment_intent);
        return redirect("/course/".$paymentIntent->metadata->course_id)->with(['error' => 'Payment Failed']);
    }
}
