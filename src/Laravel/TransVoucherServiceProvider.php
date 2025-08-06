<?php

namespace TransVoucher\Laravel;

use Illuminate\Support\ServiceProvider;
use TransVoucher\TransVoucher;

class TransVoucherServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/transvoucher.php', 'transvoucher');

        $this->app->singleton(TransVoucher::class, function ($app) {
            $config = $app['config']['transvoucher'];
            
            return new TransVoucher([
                'api_key' => $config['api_key'],
                'api_secret' => $config['api_secret'],
                'environment' => $config['environment'],
                'base_url' => $config['api_url'] ?? null,
            ]);
        });

        $this->app->alias(TransVoucher::class, 'transvoucher');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/config/transvoucher.php' => config_path('transvoucher.php'),
            ], 'transvoucher-config');
        }
    }
}