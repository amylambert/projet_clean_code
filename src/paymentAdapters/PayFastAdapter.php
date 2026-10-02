<?php

declare(strict_types=1);

final class PayFastAdapter implements IPaymentAdapter
{
    private PayFastSdk $payFastSdk;
    public function __construct() {
        $this->payFastSdk = new PayFastSdk();
    }

    public function pay(float $amount): string
    {
        $amountCents = (int) round($amount * 100);

        $payload = [
            'reference' => uniqid('pf_'),
            'amount_cents' => $amountCents,
            'currency' => 'EUR',
        ];

        $result = $this->payFastSdk->executePayment($payload);

        if (!$result['success']) {
            throw new RuntimeException('PayFast payment failed');
        }

        return $result['transaction_id'];
    }
}