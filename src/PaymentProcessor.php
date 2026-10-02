<?php

declare(strict_types=1);

const PAYMENT_ADAPTERS = [
    'stripe' => new StripeAdapter,
    'payfast' => new PayFastAdapter
];

final class PaymentProcessor
{
    # gives payment to the right adapter to pay.

    public function processPayment(string $paymentMethod, float $amount): void
    {
        foreach (PAYMENT_ADAPTERS as $key => $adapter) {
            if ($key === $paymentMethod) {
                $transactionId = $adapter->pay($amount);
                echo "PAYMENT {$transactionId}" . PHP_EOL;
                return;
            }
        }
        throw new RuntimeException('Invalid payment method.');
    }
}