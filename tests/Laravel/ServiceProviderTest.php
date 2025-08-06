<?php

namespace TransVoucher\Tests\Laravel;

use Orchestra\Testbench\TestCase;
use TransVoucher\Laravel\Facades\TransVoucher;
use TransVoucher\Laravel\TransVoucherServiceProvider;
use TransVoucher\Service\PaymentService;

class ServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [TransVoucherServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'TransVoucher' => TransVoucher::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        // Setup default testing environment variables
        $app['config']->set('transvoucher.api_key', 'test-key');
        $app['config']->set('transvoucher.api_secret', 'test-secret');
        $app['config']->set('transvoucher.environment', 'sandbox');
    }

    public function testServiceProviderRegistersConfig()
    {
        $this->assertNotNull(config('transvoucher'));
        $this->assertEquals('test-key', config('transvoucher.api_key'));
        $this->assertEquals('test-secret', config('transvoucher.api_secret'));
        $this->assertEquals('sandbox', config('transvoucher.environment'));
    }

    public function testServiceIsRegisteredAsSingleton()
    {
        $instance1 = app('transvoucher');
        $instance2 = app('transvoucher');
        
        $this->assertSame($instance1, $instance2);
    }

    public function testFacadeWorks()
    {
        // Need to get the root instance first
        $instance = TransVoucher::getFacadeRoot();
        $this->assertInstanceOf(PaymentService::class, $instance->payments);
    }

    public function testCustomApiUrlFromEnvironment()
    {
        $customUrl = 'https://custom-api.transvoucher.test/v1.0';
        
        $this->app['config']->set('transvoucher.api_url', $customUrl);
        
        $baseUrl = TransVoucher::getConfig('base_url');
        $this->assertEquals($customUrl, $baseUrl);
    }

    public function testDefaultApiUrlFallback()
    {
        // Clear any custom URL
        $this->app['config']->set('transvoucher.api_url', null);
        
        $baseUrl = TransVoucher::getConfig('base_url');
        $this->assertEquals('https://sandbox-api.transvoucher.com/v1.0', $baseUrl);
    }

    public function testPaymentServiceIntegration()
    {
        $instance = TransVoucher::getFacadeRoot();
        $payment = $instance->payments;
        
        $this->assertInstanceOf(PaymentService::class, $payment);
    }

    public function testEnvironmentOverride()
    {
        // Test production environment
        $this->app['config']->set('transvoucher.environment', 'production');
        $this->app['config']->set('transvoucher.api_url', null);
        
        $baseUrl = TransVoucher::getConfig('base_url');
        $this->assertEquals('https://api.transvoucher.com/v1.0', $baseUrl);
    }

    public function testConfigPublishing()
    {
        $this->artisan('vendor:publish', [
            '--provider' => 'TransVoucher\Laravel\TransVoucherServiceProvider',
            '--tag' => 'transvoucher-config'
        ]);

        $this->assertFileExists(config_path('transvoucher.php'));
    }
}