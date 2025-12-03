<?php

namespace TransVoucher\Service;

use TransVoucher\Http\Client;
use TransVoucher\Model\Commodity;
use TransVoucher\Exception\TransVoucherException;

/**
 * Commodity service for handling settlement commodity operations
 */
class CommodityService
{
    /**
     * @var Client
     */
    private $client;

    /**
     * Create a new CommodityService instance
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get all active settlement commodities
     *
     * @return Commodity[]
     * @throws TransVoucherException
     */
    public function all(): array
    {
        $response = $this->client->get('/commodities');

        if (!isset($response['data'])) {
            throw new TransVoucherException('Invalid response format from API');
        }

        return array_map(function ($commodityData) {
            return Commodity::fromArray($commodityData);
        }, $response['data']);
    }

    /**
     * Get all active settlement commodities as an array
     *
     * @return array
     * @throws TransVoucherException
     */
    public function allAsArray(): array
    {
        $commodities = $this->all();

        return array_map(function (Commodity $commodity) {
            return $commodity->toArray();
        }, $commodities);
    }
}
