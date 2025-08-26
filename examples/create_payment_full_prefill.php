<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Read more about this here: https://transvoucher.com/api-documentation#pre_fill

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
        // read https://transvoucher.test/api-documentation#pre_fill
        'customer_details' => [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+11234567890',
            'date_of_birth' => '1990-01-01',
            'country_of_residence' => 'US',
            // required in case country_of_residence is "US":
            'state_of_residence' => 'MT',
            // if you want to prefill card information
            'card_country_code' => 'US',
            'card_city' => 'Montana',
            'card_state_code' => 'MT',
            'card_post_code' => '12345',
            'card_street' => 'Street 123',
        ],
        // Use metadata to identify customer/session - will be returned in webhooks and API responses
        'metadata' => [
            // example data:
            'order_id' => 'order_123',
            'product' => 'Digital Product'
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