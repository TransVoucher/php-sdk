<?php

namespace TransVoucher\Model;

/**
 * Payment model representing a payment transaction
 */
class Payment
{
    /**
     * @var int|null
     */
    public $transaction_id;

    /**
     * @var string|null
     */
    public $reference_id;

    /**
     * @var string|null
     */
    public $payment_url;

    /**
     * @var float|null
     */
    public $amount;

    /**
     * @var string|null
     */
    public $currency;

    /**
     * @var string|null
     */
    public $status;

    /**
     * @var string|null
     */
    public $expires_at;

    /**
     * @var string|null
     */
    public $created_at;

    /**
     * @var string|null
     */
    public $updated_at;

    /**
     * @var string|null
     */
    public $paid_at;

    /**
     * @var array|null
     */
    public $customer_details;

    /**
     * @var array|null
     */
    public $metadata;

    /**
     * @var array|null
     */
    public $payment_details;

    /**
     * Create a new Payment instance from API response data
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): self
    {
        $payment = new static();
        
        $payment->transaction_id = $data['transaction_id'] ?? null;
        $payment->reference_id = $data['reference_id'] ?? null;
        $payment->payment_url = $data['payment_url'] ?? null;
        $payment->amount = isset($data['amount']) ? (float) $data['amount'] : null;
        $payment->currency = $data['currency'] ?? null;
        $payment->status = $data['status'] ?? null;
        $payment->expires_at = $data['expires_at'] ?? null;
        $payment->created_at = $data['created_at'] ?? null;
        $payment->updated_at = $data['updated_at'] ?? null;
        $payment->paid_at = $data['paid_at'] ?? null;
        $payment->customer_details = $data['customer_details'] ?? null;
        $payment->metadata = $data['metadata'] ?? null;
        $payment->payment_details = $data['payment_details'] ?? null;

        return $payment;
    }

    /**
     * Convert the payment to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'transaction_id' => $this->transaction_id,
            'reference_id' => $this->reference_id,
            'payment_url' => $this->payment_url,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'paid_at' => $this->paid_at,
            'customer_details' => $this->customer_details,
            'metadata' => $this->metadata,
            'payment_details' => $this->payment_details,
        ];
    }

    /**
     * Check if the payment is completed
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the payment is pending
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the payment has failed
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if the payment has expired
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }
} 