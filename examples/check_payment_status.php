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

    // Get payment status by reference ID
    $referenceId = '...'; // Replace with actual reference ID
    $payment = $transvoucher->payments->status($referenceId);

    echo "Payment Status Information:\n";
    echo "Reference ID: " . $payment->reference_id . "\n";
    echo "Transaction ID: " . $payment->transaction_id . "\n";
    echo "Amount: " . $payment->amount . " " . $payment->currency . "\n";
    echo "Status: " . $payment->status . "\n";
    echo "Created: " . $payment->created_at . "\n";
    echo "Updated: " . $payment->updated_at . "\n";
    
    if ($payment->paid_at) {
        echo "Paid: " . $payment->paid_at . "\n";
    }

    // Check payment status using helper methods
    if ($payment->isCompleted()) {
        echo "✅ Payment completed successfully!\n";
    } elseif ($payment->isPending()) {
        echo "⏳ Payment is still pending...\n";
    } elseif ($payment->isFailed()) {
        echo "❌ Payment failed.\n";
    } elseif ($payment->isExpired()) {
        echo "⏰ Payment expired.\n";
    }

    // Display customer details if available
    if ($payment->customer_details) {
        echo "\nCustomer Details:\n";
        foreach ($payment->customer_details as $key => $value) {
            echo "  {$key}: {$value}\n";
        }
    }

    // Display metadata if available
    if ($payment->metadata) {
        echo "\nMetadata:\n";
        foreach ($payment->metadata as $key => $value) {
            echo "  {$key}: {$value}\n";
        }
    }

} catch (TransVoucherException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 