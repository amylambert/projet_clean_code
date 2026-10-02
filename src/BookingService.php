<?php

declare(strict_types=1);

final class BookingService
{
    private $paymentProcessor;

    public function __construct(){
        $this->paymentProcessor = new PaymentProcessor();
    }

    public function confirm(Booking $booking, string $paymentMethod = 'stripe'): float
    {
        if (count($booking->items) === 0) {
            throw new RuntimeException('Empty booking');
        }

        if (!filter_var($booking->customer->email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Invalid email');
        }

        $total = 0.0;

        foreach ($booking->items as $item) {
            if ($item->quantity <= 0) {
                throw new RuntimeException('Invalid quantity');
            }

            $total += $item->ticket->price * $item->quantity;
        }


        if ($booking->customer->type === 'vip') {
            $vip = new Vip();
            $total = $vip->applyDiscount($total);
        }

        if ($booking->passType === '3days') {
            $threeDaysDiscount = new ThreeDaysDiscount();
            $total = $threeDaysDiscount->applyDiscount($total);
        }

        $this->paymentProcessor->processPayment($paymentMethod, $total);

        $booking->status = 'confirmed';

        echo "SQL INSERT booking={$booking->id} total={$total} status={$booking->status}" . PHP_EOL;

        $emailService = new EmailService();
        $emailService->sendConfirmation($booking->customer->email, $booking->id);

        return $total;
    }
}
