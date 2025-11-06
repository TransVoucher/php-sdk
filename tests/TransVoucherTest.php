<?php

namespace TransVoucher\Tests;

use PHPUnit\Framework\TestCase;
use TransVoucher\TransVoucher;
use TransVoucher\Exception\TransVoucherException;

class TransVoucherTest extends TestCase
{
    public function testCanCreateTransVoucherInstance()
    {
        $config = [
            'api_key' => 'test-key',
            'api_secret' => 'test-secret',
            'environment' => 'sandbox'
        ];

        $transvoucher = new TransVoucher($config);
        
        $this->assertInstanceOf(TransVoucher::class, $transvoucher);
        $this->assertEquals('sandbox', $transvoucher->getConfig('environment'));
        $this->assertEquals('https://sandbox-api.transvoucher.com/v1.0', $transvoucher->getConfig('base_url'));
    }

    public function testProductionEnvironmentSetsCorrectBaseUrl()
    {
        $config = [
            'api_key' => 'test-key',
            'api_secret' => 'test-secret',
            'environment' => 'production'
        ];

        $transvoucher = new TransVoucher($config);
        
        $this->assertEquals('https://api.transvoucher.com/v1.0', $transvoucher->getConfig('base_url'));
    }

    public function testThrowsExceptionForMissingApiKey()
    {
        $this->expectException(TransVoucherException::class);
        $this->expectExceptionMessage('Missing required config: api_key');

        new TransVoucher([
            'api_secret' => 'test-secret'
        ]);
    }

    public function testThrowsExceptionForMissingApiSecret()
    {
        $this->expectException(TransVoucherException::class);
        $this->expectExceptionMessage('Missing required config: api_secret');

        new TransVoucher([
            'api_key' => 'test-key'
        ]);
    }

    public function testThrowsExceptionForInvalidEnvironment()
    {
        $this->expectException(TransVoucherException::class);
        $this->expectExceptionMessage('Invalid environment. Must be \'sandbox\' or \'production\'');

        new TransVoucher([
            'api_key' => 'test-key',
            'api_secret' => 'test-secret',
            'environment' => 'invalid'
        ]);
    }

    public function testCanAccessPaymentsService()
    {
        $config = [
            'api_key' => 'test-key',
            'api_secret' => 'test-secret'
        ];

        $transvoucher = new TransVoucher($config);

        $this->assertInstanceOf(\TransVoucher\Service\PaymentService::class, $transvoucher->payments);
    }

    public function testCanAccessCurrenciesService()
    {
        $config = [
            'api_key' => 'test-key',
            'api_secret' => 'test-secret'
        ];

        $transvoucher = new TransVoucher($config);

        $this->assertInstanceOf(\TransVoucher\Service\CurrencyService::class, $transvoucher->currencies);
    }

    public function testThrowsExceptionForUnknownService()
    {
        $config = [
            'api_key' => 'test-key',
            'api_secret' => 'test-secret'
        ];

        $transvoucher = new TransVoucher($config);

        $this->expectException(TransVoucherException::class);
        $this->expectExceptionMessage('Unknown service: unknown');

        $transvoucher->unknown;
    }
} 