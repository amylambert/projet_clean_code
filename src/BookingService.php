<?php

declare(strict_types=1);

final class BookingService
{
    private $paymentProcessor;
    private $timer;

    public function __construct(private BookingConfirmationSubject $confirmationSubject)
    {
        $this->paymentProcessor = new PaymentProcessor();
        $this->timer = new Timer();
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

        $this->timeProcessPayment($paymentMethod, $total);

        $booking->status = 'confirmed';

        echo "SQL INSERT booking={$booking->id} total={$total} status={$booking->status}" . PHP_EOL;

        $this->confirmationSubject->notify(new BookingConfirmedEvent($booking, $total));

        return $total;
    }

    function timeProcessPayment(string $paymentMethod, float $amount): void
    {
        $this->timer->start();
        $this->paymentProcessor->processPayment($paymentMethod, $amount);
        $this->timer->stop();
        echo "Payment processed in {$this->timer->getElapsedTime()} seconds" . PHP_EOL;
    }
}
