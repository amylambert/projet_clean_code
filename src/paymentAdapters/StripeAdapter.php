<?php

declare(strict_types=1);

class StripeAdapter implements IPaymentAdapter
{
    private $stripeClient;
    public function __construct() {
        $this->stripeClient = new StripeClient();
    }

    public function pay(float $amount): string
    {
        return $this->stripeClient->charge($amount);
    }
}