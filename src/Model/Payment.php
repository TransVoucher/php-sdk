<?php

namespace TransVoucher\Model;

/**
 * Payment model representing a payment transaction or payment link
 */
class Payment
{
    /**
     * @var int|null
     */
    public $id;

    /**
     * @var int|null
     */
    public $transaction_id;

    /**
     * @var string|null Title of the payment link
     */
    public $title;

    /**
     * @var string|null Description of the payment link
     */
    public $description;

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
     * @var array|null Additional custom fields
     */
    public $custom_fields;

    /**
     * @var float|null Customer commission percentage
     */
    public $customer_commission_percentage;

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
        
        $payment->id = $data['id'] ?? null;
        $payment->transaction_id = $data['transaction_id'] ?? null;
        $payment->title = $data['title'] ?? null;
        $payment->description = $data['description'] ?? null;
        $payment->reference_id = $data['reference_id'] ?? null;
        $payment->payment_url = $data['payment_url'] ?? null;
        $payment->amount = isset($data['amount']) ? (float) $data['amount'] : null;
        $payment->currency = $data['currency'] ?? null;
        $payment->status = $data['status'] ?? null;
        $payment->expires_at = $data['expires_at'] ?? null;
        $payment->custom_fields = $data['custom_fields'] ?? null;
        $payment->customer_commission_percentage = isset($data['customer_commission_percentage']) ? 
            (float) $data['customer_commission_percentage'] : null;
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
        return array_filter([
            'id' => $this->id,
            'transaction_id' => $this->transaction_id,
            'title' => $this->title,
            'description' => $this->description,
            'reference_id' => $this->reference_id,
            'payment_url' => $this->payment_url,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'expires_at' => $this->expires_at,
            'custom_fields' => $this->custom_fields,
            'customer_commission_percentage' => $this->customer_commission_percentage,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'paid_at' => $this->paid_at,
            'customer_details' => $this->customer_details,
            'metadata' => $this->metadata,
            'payment_details' => $this->payment_details,
        ], function ($value) {
            return $value !== null;
        });
    }

    public function getId(): ?int
    {
        return $this->transaction_id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function getStatus(): ?string
    {
        return $this->status;
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