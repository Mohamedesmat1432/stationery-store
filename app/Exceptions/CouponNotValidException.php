<?php

namespace App\Exceptions;

use Exception;

class CouponNotValidException extends Exception
{
    public function __construct(string $code, string $reason = 'expired or invalid')
    {
        parent::__construct("Coupon '{$code}' is {$reason}.");
    }
}
