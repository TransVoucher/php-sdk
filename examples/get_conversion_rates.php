<?php

require_once __DIR__ . '/../vendor/autoload.php';

use TransVoucher\TransVoucher;
use TransVoucher\Exception\TransVoucherException;

try {
    // Initialize TransVoucher client
    $transvoucher = new TransVoucher([
        'api_key' => 'tvc_etKvEUFAKX1AxbuEIgtvzA0wW4dj436o',
        'api_secret' => 'tvcs_yAzhKMDyem0F4E3vPQbSqRuewiAxm8UOU99ckhlEoMfx8zvb',
        'environment' => 'production'
    ]);

    echo "TransVoucher - Get Conversion Rates Example\n";
    echo str_repeat('=', 80) . "\n\n";

    // Get all available currencies first
    echo "Fetching available currencies...\n";
    $currencies = $transvoucher->currencies->all();
    echo "Found " . count($currencies) . " currencies\n\n";

    // Configuration for conversion rate requests
    $network = 'POL';        // Network: POL (Polygon) or BSC (Binance Smart Chain)
    $commodity = 'USDT';     // Cryptocurrency: USDT
    $paymentMethod = 'card'; // Payment method: card

    echo "Getting conversion rates for $commodity on $network network via $paymentMethod\n";
    echo str_repeat('=', 80) . "\n\n";

    // Get conversion rates for all currencies
    $results = [];

    foreach ($currencies as $currency) {
        $currencyCode = $currency->getShortCode();

        try {
            echo "Fetching rate for $currencyCode... ";

            $conversionData = $transvoucher->payments->getConversionRate(
                $network,
                $commodity,
                $currencyCode,
                $paymentMethod
            );

            $results[$currencyCode] = [
                'currency' => $currency,
                'rate' => $conversionData['rate'] ?? null,
                'success' => true
            ];

            echo "✓ Rate: " . ($conversionData['rate'] ?? 'N/A') . "\n";

        } catch (TransVoucherException $e) {
            $results[$currencyCode] = [
                'currency' => $currency,
                'rate' => null,
                'success' => false,
                'error' => $e->getMessage()
            ];

            echo "✗ Error: " . $e->getMessage() . "\n";
        }

        // Small delay to avoid rate limiting
        usleep(100000); // 100ms delay
    }

    echo "\n" . str_repeat('=', 80) . "\n";
    echo "Summary of Conversion Rates\n";
    echo str_repeat('=', 80) . "\n\n";

    // Display summary table
    echo sprintf("%-6s | %-25s | %-10s | %-20s\n", "Code", "Name", "Symbol", "Rate (1 $commodity)");
    echo str_repeat('-', 80) . "\n";

    foreach ($results as $currencyCode => $data) {
        if ($data['success']) {
            echo sprintf(
                "%-6s | %-25s | %-10s | %-20s\n",
                $currencyCode,
                substr($data['currency']->getName(), 0, 25),
                $data['currency']->getSymbol(),
                $data['rate'] ?? 'N/A'
            );
        }
    }

    // Display failed conversions
    $failedCurrencies = array_filter($results, fn($data) => !$data['success']);

    if (!empty($failedCurrencies)) {
        echo "\n" . str_repeat('=', 80) . "\n";
        echo "Failed Conversions (" . count($failedCurrencies) . ")\n";
        echo str_repeat('=', 80) . "\n\n";

        foreach ($failedCurrencies as $currencyCode => $data) {
            echo "- $currencyCode: " . $data['error'] . "\n";
        }
    }

    // Example: Calculate how much USDT you get for a specific amount
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "Example Calculations\n";
    echo str_repeat('=', 80) . "\n\n";

    $exampleAmount = 100; // Amount in fiat currency
    $exampleCurrencies = ['USD', 'EUR', 'GBP'];

    foreach ($exampleCurrencies as $currencyCode) {
        if (isset($results[$currencyCode]) && $results[$currencyCode]['success']) {
            $rate = $results[$currencyCode]['rate'];
            $usdtAmount = $exampleAmount * $rate;

            echo sprintf(
                "%s %s %s = %.4f %s (Rate: %s)\n",
                $exampleAmount,
                $currencyCode,
                $results[$currencyCode]['currency']->getSymbol(),
                $usdtAmount,
                $commodity,
                $rate
            );
        }
    }

    // Example: Find best and worst rates
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "Rate Analysis\n";
    echo str_repeat('=', 80) . "\n\n";

    $successfulRates = array_filter($results, fn($data) => $data['success']);

    if (!empty($successfulRates)) {
        // Sort by rate
        uasort($successfulRates, function($a, $b) {
            return ($b['rate'] ?? 0) <=> ($a['rate'] ?? 0);
        });

        $highestRate = reset($successfulRates);
        $lowestRate = end($successfulRates);

        echo "Highest Rate (Best Value):\n";
        echo "  " . $highestRate['currency']->getShortCode() . " - " .
             $highestRate['currency']->getName() .
             " (Rate: " . $highestRate['rate'] . ")\n\n";

        echo "Lowest Rate:\n";
        echo "  " . $lowestRate['currency']->getShortCode() . " - " .
             $lowestRate['currency']->getName() .
             " (Rate: " . $lowestRate['rate'] . ")\n";
    }

    // Example: Get rates for different networks
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "Comparing Networks (USD example)\n";
    echo str_repeat('=', 80) . "\n\n";

    $networks = ['POL', 'BSC'];
    $comparisonCurrency = 'USD';

    foreach ($networks as $compareNetwork) {
        try {
            $rateData = $transvoucher->payments->getConversionRate(
                $compareNetwork,
                $commodity,
                $comparisonCurrency,
                $paymentMethod
            );

            echo "$compareNetwork: 1 $comparisonCurrency = " . ($rateData['rate'] ?? 'N/A') . " $commodity\n";
        } catch (TransVoucherException $e) {
            echo "$compareNetwork: Error - " . $e->getMessage() . "\n";
        }
    }

} catch (TransVoucherException $e) {
    echo "Error: " . $e->getMessage() . "\n";

    if ($e->getCode()) {
        echo "Error Code: " . $e->getCode() . "\n";
    }

    exit(1);
}
