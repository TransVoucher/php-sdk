# TransVoucher PHP SDK

Official PHP SDK for TransVoucher payment processing API. Accept card payments and receive cryptocurrency settlements.

## Installation

Install via Composer:

```bash
composer require transvoucher/php-sdk
```

## Requirements

- PHP 8.0 or higher
- ext-json
- GuzzleHTTP 7.0+

## Quick Start

```php
<?php
require_once 'vendor/autoload.php';

use TransVoucher\TransVoucher;

// Initialize the client
$transvoucher = new TransVoucher([
    'api_key' => 'your-api-key',
    'api_secret' => 'your-api-secret',
    'environment' => 'production' // or 'sandbox'
]);

// Create a payment
$payment = $transvoucher->payments->create([
    'amount' => 99.99,
    'currency' => 'USD',
    'customer_email' => 'customer@example.com',
    'redirect_url' => 'https://yourstore.com/success',
    'close_url' => 'https://yourstore.com/cancel'
]);

// Redirect to payment page
header('Location: ' . $payment->payment_url);
exit;
```

## Configuration

### Environment

The SDK supports two environments:

- `sandbox` - For testing (https://sandbox-api.transvoucher.com)
- `production` - For live transactions (https://api.transvoucher.com)

### API Credentials

Get your API credentials from your TransVoucher merchant dashboard:

1. Log in to your merchant account
2. Navigate to Sales Channels
3. Generate API credentials for your sales channel

## Payment Methods

### Create Payment

```php
$payment = $transvoucher->payments->create([
    'amount' => 100.00,
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

echo "Payment URL: " . $payment->payment_url;
echo "Reference ID: " . $payment->reference_id;
```

### Get Payment Status

```php
$payment = $transvoucher->payments->status('txn_abc123def456');

echo "Status: " . $payment->status;
echo "Amount: " . $payment->amount;
echo "Currency: " . $payment->currency;
```

### List Payments

```php
$payments = $transvoucher->payments->list([
    'limit' => 20,
    'status' => 'completed',
    'from_date' => '2024-01-01',
    'to_date' => '2024-01-31'
]);

foreach ($payments->payments as $payment) {
    echo "Payment {$payment->reference_id}: {$payment->status}\n";
}

// Handle pagination
if ($payments->has_more) {
    $nextPage = $transvoucher->payments->list([
        'page_token' => $payments->next_page_token
    ]);
}
```

## Webhook Handling

### Verify Webhook Signature

```php
<?php
// webhook.php
require_once 'vendor/autoload.php';

use TransVoucher\Webhook;

$webhook = new Webhook('your-webhook-secret');

$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_TRANSVOUCHER_SIGNATURE'] ?? '';

if ($webhook->verifySignature($payload, $signature)) {
    $event = json_decode($payload, true);
    
    switch ($event['type']) {
        case 'payment.completed':
            // Handle successful payment
            $payment = $event['data'];
            echo "Payment completed: " . $payment['reference_id'];
            break;
            
        case 'payment.failed':
            // Handle failed payment
            $payment = $event['data'];
            echo "Payment failed: " . $payment['reference_id'];
            break;
            
        case 'payment.refunded':
            // Handle refunded payment
            $payment = $event['data'];
            echo "Payment refunded: " . $payment['reference_id'];
            break;
            
        case 'settlement.processed':
            // Handle settlement completion
            $settlement = $event['data'];
            echo "Settlement processed: " . $settlement['transaction_id'];
            break;
    }
} else {
    http_response_code(400);
    echo "Invalid signature";
}
?>
```

## Error Handling

```php
use TransVoucher\Exception\TransVoucherException;
use TransVoucher\Exception\AuthenticationException;
use TransVoucher\Exception\InvalidRequestException;
use TransVoucher\Exception\ApiException;

try {
    $payment = $transvoucher->payments->create([
        'amount' => 99.99,
        'currency' => 'USD'
    ]);
} catch (AuthenticationException $e) {
    echo "Authentication failed: " . $e->getMessage();
} catch (InvalidRequestException $e) {
    echo "Invalid request: " . $e->getMessage();
} catch (ApiException $e) {
    echo "API error: " . $e->getMessage();
} catch (TransVoucherException $e) {
    echo "TransVoucher error: " . $e->getMessage();
}
```

## Testing

### Sandbox Environment

```php
$transvoucher = new TransVoucher([
    'api_key' => 'sandbox-api-key',
    'api_secret' => 'sandbox-api-secret', 
    'environment' => 'sandbox'
]);
```

### Test Card Numbers

| Card Number | Brand | Result |
|-------------|-------|--------|
| 4242424242424242 | Visa | Success |
| 4000000000000002 | Visa | Declined |
| 5555555555554444 | Mastercard | Success |
| 4000000000000069 | Visa | Expired Card |

## API Reference

### TransVoucher Client

```php
$client = new TransVoucher([
    'api_key' => 'your-api-key',
    'api_secret' => 'your-api-secret',
    'environment' => 'production', // 'sandbox' or 'production'
    'timeout' => 30 // Request timeout in seconds
]);
```

### Payments

#### Create Payment

- `amount` (required): Payment amount (minimum 0.01)
- `currency` (optional): Currency code (USD, EUR, GBP) - default: USD
- `customer_email` (optional): Customer email address
- `redirect_url` (optional): Success redirect URL
- `close_url` (optional): Cancel/close redirect URL
- `customer_details` (optional): Customer information object
- `metadata` (optional): Additional metadata for the payment
- `theme` (optional): UI theme customization
- `lang` (optional): Language code (en, es, fr, de, it, pt, ru, zh, ja, ko)

#### Payment Status

- `status`: pending, completed, failed, expired
- `amount`: Payment amount
- `currency`: Payment currency
- `reference_id`: Unique payment reference
- `transaction_id`: Internal transaction ID
- `created_at`: Payment creation timestamp
- `updated_at`: Last update timestamp
- `paid_at`: Payment completion timestamp (if completed)

## Support

- **Documentation**: [https://transvoucher.com/api-documentation](https://transvoucher.com/api-documentation)
- **Email**: developers@transvoucher.com
- **Telegram**: Contact via our support channel

## License

This SDK is released under the MIT License. See [LICENSE](LICENSE) for details. 