<?php

require_once __DIR__ . '/../vendor/autoload.php';

use TransVoucher\TransVoucher;
use TransVoucher\Exception\TransVoucherException;

try {
    // Initialize TransVoucher client
    $transvoucher = new TransVoucher([
        'api_key' => 'your-api-key',
        'api_secret' => 'your-api-secret',
        'environment' => 'sandbox'
    ]);

    // List payments with filtering
    $payments = $transvoucher->payments->list([
        'limit' => 10,
        'status' => 'completed',
        'from_date' => '2024-01-01',
        'to_date' => '2024-12-31'
    ]);

    echo "Payment List:\n";
    echo "Total payments in this page: " . $payments->getCount() . "\n";
    echo "Has more pages: " . ($payments->hasMore() ? 'Yes' : 'No') . "\n";
    
    if ($payments->hasMore()) {
        echo "Next page token: " . $payments->getNextPageToken() . "\n";
    }
    
    echo "\nPayments:\n";
    echo str_repeat('-', 80) . "\n";

    foreach ($payments->payments as $payment) {
        echo "Reference ID: " . $payment->reference_id . "\n";
        echo "Amount: " . $payment->amount . " " . $payment->currency . "\n";
        echo "Status: " . $payment->status . "\n";
        echo "Created: " . $payment->created_at . "\n";
        
        if ($payment->customer_details && isset($payment->customer_details['email'])) {
            echo "Customer: " . $payment->customer_details['email'] . "\n";
        }
        
        echo str_repeat('-', 80) . "\n";
    }

    // Example of pagination - get next page if available
    if ($payments->hasMore()) {
        echo "\nFetching next page...\n";
        
        $nextPage = $transvoucher->payments->list([
            'limit' => 10,
            'page_token' => $payments->getNextPageToken()
        ]);
        
        echo "Next page has " . $nextPage->getCount() . " payments\n";
    }

    // Example of filtering by different criteria
    echo "\nFetching failed payments...\n";
    $failedPayments = $transvoucher->payments->list([
        'status' => 'failed',
        'limit' => 5
    ]);
    
    echo "Found " . $failedPayments->getCount() . " failed payments\n";

} catch (TransVoucherException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 