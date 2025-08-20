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
    'title' => 'Payment for Order #123', // Required - title of the payment link
    'customer_details' => [
        'full_name' => 'John Doe', // Required if customer_details is provided
        'email' => 'customer@example.com' // Optional
    ],
    'redirect_url' => 'https://yourstore.com/success',
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
    'title' => 'Digital Product Purchase', // Required - title of the payment link
    'redirect_url' => 'https://yourstore.com/success',
    'customer_details' => [
        // learn more about this at: https://transvoucher.com/api-documentation#pre_fill or examples/create_payment_full_prefill.php
        'full_name' => 'John Doe',           // Required if customer_details is provided
        'id' => 'cust_123',                  // Optional - Customer's unique identifier
        'email' => 'john@example.com',       // Optional
        'phone' => '+1234567890',            // Optional
        'date_of_birth' => '1990-01-01',     // Optional - YYYY-MM-DD format
        'country_of_residence' => 'US',      // Optional - ISO country code
        'state_of_residence' => 'CA'         // Optional - Required for US customers
    ],
    'metadata' => [
        // Optional - use this to identify the customer or payment session
        // This data will be returned in webhooks and API responses
        'order_id' => 'order_123',
        'product_id' => 'prod_456',
        'user_id' => 'user_789',
        'session_id' => 'sess_abc'
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
    
    switch ($event['event']) {
        case 'payment_intent.created':
            // Handle successful payment
            $transaction = $event['data']['transaction'];
            echo "Payment intent created: " . $transaction['reference_id'];
            break;
        case 'payment_intent.succeeded':
            // Handle successful payment
            $transaction = $event['data']['transaction'];
            echo "Payment completed: " . $transaction['reference_id'];
            break;
            
        case 'payment_intent.failed':
            // Handle failed payment
            $transaction = $event['data']['transaction'];
            echo "Payment failed: " . $transaction['reference_id'];
            break;
            
        case 'payment_intent.cancelled':
            // Handle cancelled payment
            $transaction = $event['data']['transaction'];
            echo "Payment cancelled: " . $transaction['reference_id'];
            break;
            
        case 'payment_intent.expired':
            // Handle expired payment
            $transaction = $event['data']['transaction'];
            echo "Payment expired: " . $transaction['reference_id'];
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

### Create Payment

- `amount` (required): Payment amount (minimum 0.01)
- `currency` (required): Currency code (USD, EUR)
- `title` (required): Title of the payment link
- `description` (optional): Description of the payment
- `redirect_url` (optional): Success redirect URL (uses sales channel configuration if empty)
- `customer_details` (optional): Customer information object
  - `full_name` (required if customer_details provided): Customer's full name
  - `id` (optional): Customer's unique identifier
  - `email` (optional): Customer's email address
  - `phone` (optional): Customer's phone number
  - `date_of_birth` (optional): Customer's date of birth (YYYY-MM-DD format)
  - `country_of_residence` (optional): Customer's country code (ISO format, e.g., 'US', 'GB')
  - `state_of_residence` (optional): Required if country_of_residence is 'US'
- `metadata` (optional): Use this to identify the customer or payment session (returned in webhooks and API responses)
- `theme` (optional): UI theme customization
- `lang` (optional): Language code (en, es, fr, de, it, pt, ru, zh, ja, ko)
- `multiple_use` (optional): If payment link is meant for one or multiple payments

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

- **API Documentation**: [https://transvoucher.com/api-documentation](https://transvoucher.com/api-documentation)
- **Service Availability**: [https://transvoucher.com/api-documentation/service-availability](https://transvoucher.com/api-documentation/service-availability)
- **Email**: developers@transvoucher.com
- **Telegram**: @kevin_tvoucher

## Laravel Integration

### Installation

The SDK includes built-in Laravel support. After installing via Composer, add the service provider to your `config/app.php` providers array:

```php
'providers' => [
    // ...
    TransVoucher\Laravel\TransVoucherServiceProvider::class,
],
```

Add the facade to your aliases array:

```php
'aliases' => [
    // ...
    'TransVoucher' => TransVoucher\Laravel\Facades\TransVoucher::class,
],
```

### Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="TransVoucher\Laravel\TransVoucherServiceProvider" --tag="transvoucher-config"
```

This will create a `config/transvoucher.php` file. You can also use environment variables in your `.env` file:

```env
TRANSVOUCHER_API_KEY=your-api-key
TRANSVOUCHER_API_SECRET=your-api-secret
TRANSVOUCHER_ENVIRONMENT=sandbox
TRANSVOUCHER_API_URL=https://custom-api.transvoucher.com/v1.0  # Optional
```

### Usage with Laravel

Using the Facade:

```php
use TransVoucher\Laravel\Facades\TransVoucher;

// Create a payment
$payment = TransVoucher::payments->create([
    'amount' => 99.99,
    'currency' => 'USD',
    'customer_email' => 'customer@example.com',
]);

// Check payment status
$status = TransVoucher::payments->status('ref_123');

// List payments
$payments = TransVoucher::payments->list([
    'limit' => 10,
    'status' => 'completed'
]);
```

Using Dependency Injection:

```php
use TransVoucher\TransVoucher;

class PaymentController extends Controller
{
    public function store(Request $request, TransVoucher $transvoucher)
    {
        $payment = $transvoucher->payments->create([
            'amount' => $request->amount,
            'currency' => 'USD',
        ]);

        return redirect($payment->payment_url);
    }
}
```

### Laravel Webhook Handling

Create a webhook controller:

```php
use Illuminate\Http\Request;
use TransVoucher\Laravel\Facades\TransVoucher;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('X-Webhook-Signature');

        if (!$this->verifySignature($payload, $signature)) {
            abort(400, 'Invalid signature');
        }

        switch ($payload['type']) {
            case 'payment_intent.succeeded':
                // Handle successful payment
                $payment = $payload['data'];
                event(new PaymentCompletedEvent($payment));
                break;

            case 'payment_intent.failed':
                // Handle failed payment
                event(new PaymentFailedEvent($payload['data']));
                break;
        }

        return response()->json(['message' => 'Webhook processed']);
    }
}
```

### Testing in Laravel

The SDK includes Laravel-specific test helpers. In your tests:

```php
use TransVoucher\Laravel\Facades\TransVoucher;

class PaymentTest extends TestCase
{
    public function test_can_create_payment()
    {
        $payment = TransVoucher::payments->create([
            'amount' => 99.99,
            'currency' => 'USD',
        ]);

        $this->assertNotNull($payment->id);
        $this->assertEquals(99.99, $payment->amount);
        $this->assertEquals('USD', $payment->currency);
    }
}
```

## License

This SDK is released under the MIT License. See [LICENSE](LICENSE) for details. 