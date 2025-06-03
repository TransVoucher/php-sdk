<?php

require_once __DIR__ . '/../vendor/autoload.php';

use TransVoucher\Webhook;
use TransVoucher\Exception\TransVoucherException;

try {
    // Initialize webhook handler with your webhook secret
    $webhook = new Webhook('your-webhook-secret');

    // Get the raw payload and signature
    $payload = file_get_contents('php://input');
    $signature = $_SERVER['HTTP_X_TRANSVOUCHER_SIGNATURE'] ?? '';

    // Verify and parse the webhook
    $event = $webhook->verifyAndParse($payload, $signature);

    // Get event type and data
    $eventType = $webhook->getEventType($event);
    $eventData = $webhook->getEventData($event);

    // Log the webhook event
    error_log("Received webhook: {$eventType}");

    // Handle different event types
    switch ($eventType) {
        case 'payment.completed':
            handlePaymentCompleted($eventData);
            break;
            
        case 'payment.failed':
            handlePaymentFailed($eventData);
            break;
            
        case 'payment.refunded':
            handlePaymentRefunded($eventData);
            break;
            
        case 'settlement.processed':
            handleSettlementProcessed($eventData);
            break;
            
        default:
            error_log("Unknown webhook event type: {$eventType}");
            break;
    }

    // Alternative way using helper methods
    if ($webhook->isPaymentCompleted($event)) {
        // Handle payment completion
        echo "Payment completed: " . $eventData['reference_id'] . "\n";
    } elseif ($webhook->isPaymentFailed($event)) {
        // Handle payment failure
        echo "Payment failed: " . $eventData['reference_id'] . "\n";
    }

    // Return 200 OK to acknowledge receipt
    http_response_code(200);
    echo "OK";

} catch (TransVoucherException $e) {
    error_log("Webhook error: " . $e->getMessage());
    http_response_code(400);
    echo "Error: " . $e->getMessage();
} catch (Exception $e) {
    error_log("Unexpected error: " . $e->getMessage());
    http_response_code(500);
    echo "Internal server error";
}

/**
 * Handle payment completed event
 */
function handlePaymentCompleted(array $paymentData): void
{
    $referenceId = $paymentData['reference_id'];
    $amount = $paymentData['amount'];
    $currency = $paymentData['currency'];
    
    echo "Processing completed payment: {$referenceId} for {$amount} {$currency}\n";
    
    // Update your database
    // Send confirmation email to customer
    // Fulfill the order
    // etc.
    
    // Example database update (pseudo-code)
    // updateOrderStatus($paymentData['metadata']['order_id'], 'completed');
    // sendConfirmationEmail($paymentData['customer_details']['email']);
}

/**
 * Handle payment failed event
 */
function handlePaymentFailed(array $paymentData): void
{
    $referenceId = $paymentData['reference_id'];
    
    echo "Processing failed payment: {$referenceId}\n";
    
    // Update your database
    // Send failure notification
    // etc.
    
    // Example database update (pseudo-code)
    // updateOrderStatus($paymentData['metadata']['order_id'], 'failed');
    // sendFailureNotification($paymentData['customer_details']['email']);
}

/**
 * Handle payment refunded event
 */
function handlePaymentRefunded(array $paymentData): void
{
    $referenceId = $paymentData['reference_id'];
    $amount = $paymentData['amount'];
    
    echo "Processing refunded payment: {$referenceId} for {$amount}\n";
    
    // Update your database
    // Send refund confirmation
    // etc.
}

/**
 * Handle settlement processed event
 */
function handleSettlementProcessed(array $settlementData): void
{
    $transactionId = $settlementData['transaction_id'];
    
    echo "Processing settlement: {$transactionId}\n";
    
    // Update your records
    // Process crypto settlement
    // etc.
} 