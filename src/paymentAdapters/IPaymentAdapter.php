<?php

declare(strict_types=1);

interface IPaymentAdapter
{
    public function pay(float $amount): string;
}