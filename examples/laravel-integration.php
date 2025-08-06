<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use TransVoucher\Laravel\Facades\TransVoucher;
use App\Models\Order;

class PaymentController extends Controller
{
    /**
     * Create a new payment for an order
     */
    public function createPayment(Request $request, Order $order)
    {
        $request->validate([
            'customer_email' => 'required|email',
            'customer_name' => 'required|string',
        ]);

        try {
            // Create payment using the facade
            $payment = TransVoucher::payments->create([
                'amount' => $order->total_amount,
                'currency' => 'USD',
                'title' => "Order #{$order->id}",
                'description' => "Payment for order #{$order->id}",
                'customer_email' => $request->customer_email,
                'customer_name' => $request->customer_name,
                'redirect_url' => route('payment.success', $order),
                'metadata' => [
                    'order_id' => $order->id,
                    'customer_id' => $order->customer_id,
                ],
            ]);

            // Store payment reference
            $order->update([
                'payment_id' => $payment->id,
                'payment_reference' => $payment->reference_id,
                'payment_url' => $payment->payment_url,
            ]);

            return redirect($payment->payment_url);

        } catch (\TransVoucher\Exception\TransVoucherException $e) {
            return back()->withErrors(['payment' => $e->getMessage()]);
        }
    }

    /**
     * Handle payment webhook
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('X-Webhook-Signature');

        try {
            // Handle the webhook event
            switch ($payload['type']) {
                case 'payment.completed':
                    $payment = $payload['data'];
                    $order = Order::where('payment_reference', $payment['reference_id'])->firstOrFail();
                    
                    $order->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);

                    // Fire payment completed event
                    event(new PaymentCompletedEvent($order));
                    break;

                case 'payment.failed':
                    $payment = $payload['data'];
                    $order = Order::where('payment_reference', $payment['reference_id'])->firstOrFail();
                    
                    $order->update(['status' => 'payment_failed']);
                    event(new PaymentFailedEvent($order));
                    break;
            }

            return response()->json(['message' => 'Webhook processed successfully']);

        } catch (\Exception $e) {
            report($e);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Check payment status
     */
    public function checkStatus(Order $order)
    {
        try {
            $payment = TransVoucher::payments->status($order->payment_reference);

            return response()->json([
                'status' => $payment->status,
                'amount' => $payment->amount,
                'paid_at' => $payment->paid_at,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * List recent payments
     */
    public function listPayments()
    {
        try {
            $payments = TransVoucher::payments->list([
                'limit' => 10,
                'status' => 'completed',
            ]);

            return view('payments.index', [
                'payments' => $payments->getPayments(),
                'hasMore' => $payments->hasMore(),
                'nextPageToken' => $payments->getNextPageToken(),
            ]);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}