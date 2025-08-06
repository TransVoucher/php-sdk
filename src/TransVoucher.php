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
    public const string API_VERSION = 'v1.0';

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
        // Check for environment variables first
        $envConfig = [
            'api_key' => getenv('TRANSVOUCHER_API_KEY'),
            'api_secret' => getenv('TRANSVOUCHER_API_SECRET'),
            'environment' => getenv('TRANSVOUCHER_ENVIRONMENT'),
            'base_url' => getenv('TRANSVOUCHER_API_URL'),
        ];

        // Remove null values from env config
        $envConfig = array_filter($envConfig, function ($value) {
            return $value !== false && $value !== null;
        });

        $defaults = [
            'environment' => 'production',
            'timeout' => 30,
            'connect_timeout' => 10,
            'user_agent' => 'TransVoucher-PHP-SDK/1.0.0',
            'base_url' => null,
        ];

        // Merge in order of precedence: defaults -> env -> explicit config
        $merged = array_merge($defaults, $envConfig, $config);

        // Set base URL based on environment if not explicitly provided
        if (!$merged['base_url']) {
            $merged['base_url'] = $merged['environment'] === 'sandbox' 
                ? 'https://sandbox-api.transvoucher.com/' . self::API_VERSION
                : 'https://api.transvoucher.com/' . self::API_VERSION;
        }

        // Ensure base_url doesn't end with a slash and includes API version
        $merged['base_url'] = rtrim($merged['base_url'], '/');
        if (!str_ends_with($merged['base_url'], self::API_VERSION)) {
            $merged['base_url'] .= '/' . self::API_VERSION;
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