<?php

namespace TransVoucher\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \TransVoucher\Service\PaymentService payments()
 * @method static \TransVoucher\Http\Client getClient()
 * @method static mixed getConfig(string $key, $default = null)
 *
 * @see \TransVoucher\TransVoucher
 */
class TransVoucher extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'transvoucher';
    }
}