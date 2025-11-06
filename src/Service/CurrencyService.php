<?php

namespace TransVoucher\Service;

use TransVoucher\Http\Client;
use TransVoucher\Model\Currency;
use TransVoucher\Exception\TransVoucherException;

/**
 * Currency service for handling currency operations
 */
class CurrencyService
{
    /**
     * @var Client
     */
    private $client;

    /**
     * Create a new CurrencyService instance
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get all active processing currencies
     *
     * @return Currency[]
     * @throws TransVoucherException
     */
    public function all(): array
    {
        $response = $this->client->get('/currencies');

        if (!isset($response['data'])) {
            throw new TransVoucherException('Invalid response format from API');
        }

        return array_map(function ($currencyData) {
            return Currency::fromArray($currencyData);
        }, $response['data']);
    }

    /**
     * Get all active processing currencies as an array
     *
     * @return array
     * @throws TransVoucherException
     */
    public function allAsArray(): array
    {
        $currencies = $this->all();

        return array_map(function (Currency $currency) {
            return $currency->toArray();
        }, $currencies);
    }
}
