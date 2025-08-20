<?php

namespace TransVoucher\Service;

use TransVoucher\Http\Client;
use TransVoucher\Model\Payment;
use TransVoucher\Model\PaymentList;
use TransVoucher\Exception\InvalidRequestException;
use TransVoucher\Exception\TransVoucherException;

/**
 * Payment service for handling payment operations
 */
class PaymentService
{
    /**
     * @var Client
     */
    private $client;

    /**
     * Create a new PaymentService instance
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Create a new payment
     *
     * @param array $params Payment parameters
     * @return Payment
     * @throws TransVoucherException
     */
    public function create(array $params): Payment
    {
        $this->validateCreateParams($params);
        
        $response = $this->client->post('/payment/create', $params);
        
        if (!isset($response['data'])) {
            throw new TransVoucherException('Invalid response format from API');
        }

        return Payment::fromArray($response['data']);
    }

    /**
     * Get payment status by reference ID
     *
     * @param string $referenceId Payment reference ID
     * @return Payment
     * @throws TransVoucherException
     */
    public function status(string $referenceId): Payment
    {
        if (empty($referenceId)) {
            throw new InvalidRequestException('Reference ID is required');
        }

        $response = $this->client->get("/payment/status/{$referenceId}");
        
        if (!isset($response['data'])) {
            throw new TransVoucherException('Invalid response format from API');
        }

        return Payment::fromArray($response['data']);
    }

    /**
     * List payments with optional filtering and pagination
     *
     * @param array $params Query parameters
     * @return PaymentList
     * @throws TransVoucherException
     */
    public function list(array $params = []): PaymentList
    {
        $this->validateListParams($params);
        
        $response = $this->client->get('/payment/list', $params);
        
        if (!isset($response['data'])) {
            throw new TransVoucherException('Invalid response format from API');
        }

        return PaymentList::fromArray($response['data']);
    }

    /**
     * Validate parameters for creating a payment
     *
     * @param array $params
     * @throws InvalidRequestException
     */
    private function validateCreateParams(array $params): void
    {
        // Amount is required
        if (!isset($params['amount'])) {
            throw new InvalidRequestException('Amount is required');
        }

        $amount = $params['amount'];
        if (!is_numeric($amount) || $amount < 0.01) {
            throw new InvalidRequestException('Amount must be a number greater than or equal to 0.01');
        }

        // Validate currency if provided
        if (isset($params['currency'])) {
            $validCurrencies = ['USD', 'EUR'];
            if (!in_array(strtoupper($params['currency']), $validCurrencies)) {
                throw new InvalidRequestException('Currency must be one of: ' . implode(', ', $validCurrencies));
            }
        }

        // Validate URLs if provided
        if (isset($params['redirect_url']) && !filter_var($params['redirect_url'], FILTER_VALIDATE_URL)) {
            throw new InvalidRequestException('Invalid redirect URL');
        }

        // Validate language if provided
        if (isset($params['lang'])) {
            $validLanguages = ['en', 'es', 'fr', 'de', 'it', 'pt', 'ru', 'zh', 'ja', 'ko'];
            if (!in_array($params['lang'], $validLanguages)) {
                throw new InvalidRequestException('Language must be one of: ' . implode(', ', $validLanguages));
            }
        }

        // Validate title length if provided
        if (isset($params['title']) && strlen($params['title']) > 255) {
            throw new InvalidRequestException('Title must not exceed 255 characters');
        }

        // Validate description length if provided
        if (isset($params['description']) && strlen($params['description']) > 1000) {
            throw new InvalidRequestException('Description must not exceed 1000 characters');
        }

        // Validate expiration date if provided
        if (isset($params['expires_at'])) {
            $expiresAt = strtotime($params['expires_at']);
            if ($expiresAt === false || $expiresAt <= time()) {
                throw new InvalidRequestException('Expiration date must be in the future');
            }
        }

        // Validate custom fields if provided
        if (isset($params['custom_fields']) && !is_array($params['custom_fields'])) {
            throw new InvalidRequestException('Custom fields must be an array');
        }

        // Validate email if provided
        if (isset($params['customer_email']) && !filter_var($params['customer_email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidRequestException('Invalid email address');
        }
    }

    /**
     * Validate parameters for listing payments
     *
     * @param array $params
     * @throws InvalidRequestException
     */
    private function validateListParams(array $params): void
    {
        // Validate limit
        if (isset($params['limit'])) {
            $limit = $params['limit'];
            if (!is_int($limit) || $limit < 1 || $limit > 100) {
                throw new InvalidRequestException('Limit must be an integer between 1 and 100');
            }
        }

        // Validate status
        if (isset($params['status'])) {
            $validStatuses = ['pending', 'completed', 'failed', 'expired'];
            if (!in_array($params['status'], $validStatuses)) {
                throw new InvalidRequestException('Status must be one of: ' . implode(', ', $validStatuses));
            }
        }

        // Validate dates
        if (isset($params['from_date'])) {
            if (!$this->isValidDate($params['from_date'])) {
                throw new InvalidRequestException('from_date must be in YYYY-MM-DD format');
            }
        }

        if (isset($params['to_date'])) {
            if (!$this->isValidDate($params['to_date'])) {
                throw new InvalidRequestException('to_date must be in YYYY-MM-DD format');
            }
        }

        // Validate date range
        if (isset($params['from_date']) && isset($params['to_date'])) {
            if (strtotime($params['from_date']) > strtotime($params['to_date'])) {
                throw new InvalidRequestException('from_date must be before or equal to to_date');
            }
        }
    }

    /**
     * Check if a date string is valid (YYYY-MM-DD format)
     *
     * @param string $date
     * @return bool
     */
    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
} 