<?php

namespace TransVoucher;

use TransVoucher\Exception\TransVoucherException;

/**
 * Webhook utility class for verifying webhook signatures
 */
class Webhook
{
    /**
     * @var string
     */
    private $secret;

    /**
     * Create a new Webhook instance
     *
     * @param string $secret Webhook secret for signature verification
     */
    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    /**
     * Verify webhook signature
     *
     * @param string $payload Raw webhook payload
     * @param string $signature Signature from X-TransVoucher-Signature header
     * @return bool
     */
    public function verifySignature(string $payload, string $signature): bool
    {
        if (empty($this->secret)) {
            throw new TransVoucherException('Webhook secret is required for signature verification');
        }

        if (empty($signature)) {
            return false;
        }

        // Remove 'sha256=' prefix if present
        $signature = str_replace('sha256=', '', $signature);

        // Compute expected signature
        $expectedSignature = hash_hmac('sha256', $payload, $this->secret);

        // Use timing-safe comparison
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Parse webhook payload
     *
     * @param string $payload Raw webhook payload
     * @return array
     * @throws TransVoucherException
     */
    public function parsePayload(string $payload): array
    {
        $data = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new TransVoucherException('Invalid JSON in webhook payload');
        }

        return $data;
    }

    /**
     * Verify and parse webhook
     *
     * @param string $payload Raw webhook payload
     * @param string $signature Signature from X-TransVoucher-Signature header
     * @return array
     * @throws TransVoucherException
     */
    public function verifyAndParse(string $payload, string $signature): array
    {
        if (!$this->verifySignature($payload, $signature)) {
            throw new TransVoucherException('Invalid webhook signature');
        }

        return $this->parsePayload($payload);
    }

    /**
     * Get webhook event type from parsed payload
     *
     * @param array $payload Parsed webhook payload
     * @return string|null
     */
    public function getEventType(array $payload): ?string
    {
        return $payload['type'] ?? null;
    }

    /**
     * Get webhook event data from parsed payload
     *
     * @param array $payload Parsed webhook payload
     * @return array|null
     */
    public function getEventData(array $payload): ?array
    {
        return $payload['data'] ?? null;
    }

    /**
     * Check if webhook event is a payment completion
     *
     * @param array $payload Parsed webhook payload
     * @return bool
     */
    public function isPaymentCompleted(array $payload): bool
    {
        return $this->getEventType($payload) === 'payment.completed';
    }

    /**
     * Check if webhook event is a payment failure
     *
     * @param array $payload Parsed webhook payload
     * @return bool
     */
    public function isPaymentFailed(array $payload): bool
    {
        return $this->getEventType($payload) === 'payment.failed';
    }

    /**
     * Check if webhook event is a payment refund
     *
     * @param array $payload Parsed webhook payload
     * @return bool
     */
    public function isPaymentRefunded(array $payload): bool
    {
        return $this->getEventType($payload) === 'payment.refunded';
    }

    /**
     * Check if webhook event is a settlement processing
     *
     * @param array $payload Parsed webhook payload
     * @return bool
     */
    public function isSettlementProcessed(array $payload): bool
    {
        return $this->getEventType($payload) === 'settlement.processed';
    }
} 