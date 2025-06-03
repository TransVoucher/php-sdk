<?php

namespace TransVoucher\Exception;

use Exception;

/**
 * Base exception class for all TransVoucher SDK exceptions
 */
class TransVoucherException extends Exception
{
    /**
     * Create a new TransVoucherException instance
     *
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct(string $message = '', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
} 