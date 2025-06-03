<?php

namespace TransVoucher;

use TransVoucher\Service\PaymentService;
use TransVoucher\Http\Client;

/**
 * TransVoucher PHP SDK
 * 
 * @property PaymentService $payments
 */
class TransVoucher
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var PaymentService
     */
    private $payments;

    /**
     * @var array
     */
    private $config;

    /**
     * Create a new TransVoucher instance
     *
     * @param array $config Configuration array
     * @throws Exception\TransVoucherException
     */
    public function __construct(array $config = [])
    {
        $this->validateConfig($config);
        $this->config = $this->mergeDefaultConfig($config);
        $this->client = new Client($this->config);
        $this->initializeServices();
    }

    /**
     * Get the HTTP client instance
     *
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Get configuration value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getConfig(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Magic getter for services
     *
     * @param string $name
     * @return mixed
     * @throws Exception\TransVoucherException
     */
    public function __get(string $name)
    {
        switch ($name) {
            case 'payments':
                return $this->payments;
            default:
                throw new Exception\TransVoucherException("Unknown service: {$name}");
        }
    }

    /**
     * Validate the configuration
     *
     * @param array $config
     * @throws Exception\TransVoucherException
     */
    private function validateConfig(array $config): void
    {
        $required = ['api_key', 'api_secret'];
        
        foreach ($required as $key) {
            if (empty($config[$key])) {
                throw new Exception\TransVoucherException("Missing required config: {$key}");
            }
        }

        if (isset($config['environment']) && !in_array($config['environment'], ['sandbox', 'production'])) {
            throw new Exception\TransVoucherException("Invalid environment. Must be 'sandbox' or 'production'");
        }
    }

    /**
     * Merge with default configuration
     *
     * @param array $config
     * @return array
     */
    private function mergeDefaultConfig(array $config): array
    {
        $defaults = [
            'environment' => 'production',
            'timeout' => 30,
            'connect_timeout' => 10,
            'user_agent' => 'TransVoucher-PHP-SDK/1.0.0',
            'base_url' => null, // Will be set based on environment
        ];

        $merged = array_merge($defaults, $config);

        // Set base URL based on environment if not explicitly provided
        if (!$merged['base_url']) {
            $merged['base_url'] = $merged['environment'] === 'sandbox' 
                ? 'https://sandbox-api.transvoucher.com/v1.0'
                : 'https://api.transvoucher.com/v1.0';
        }

        return $merged;
    }

    /**
     * Initialize service instances
     */
    private function initializeServices(): void
    {
        $this->payments = new PaymentService($this->client);
    }
} 