<?php

require_once __DIR__ . '/../vendor/autoload.php';

use TransVoucher\TransVoucher;
use TransVoucher\Exception\TransVoucherException;

try {
    // Initialize TransVoucher client
    $transvoucher = new TransVoucher([
        'api_key' => 'your-api-key',
        'api_secret' => 'your-api-secret',
        'environment' => 'sandbox' // Use 'production' for live transactions
    ]);

    // Create a payment
    $payment = $transvoucher->payments->create([
        'amount' => 99.99,
        'currency' => 'USD',
        'customer_email' => 'customer@example.com',
        'redirect_url' => 'https://yourstore.com/success',
        'close_url' => 'https://yourstore.com/cancel',
        'customer_details' => [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ],
        'metadata' => [
            'order_id' => 'order_123',
            'product' => 'Digital Product'
        ],
        'theme' => [
            'color' => '#6366f1'
        ],
        'lang' => 'en'
    ]);

    echo "Payment created successfully!\n";
    echo "Payment URL: " . $payment->payment_url . "\n";
    echo "Reference ID: " . $payment->reference_id . "\n";
    echo "Transaction ID: " . $payment->transaction_id . "\n";
    echo "Amount: " . $payment->amount . " " . $payment->currency . "\n";
    echo "Status: " . $payment->status . "\n";
    
    // In a web application, you would redirect the user to the payment URL
    // header('Location: ' . $payment->payment_url);
    // exit;

} catch (TransVoucherException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 