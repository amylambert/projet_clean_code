<?php

declare(strict_types=1);

final class EmailObserver implements IBookingObserver
{
    public function __construct(private EmailService $emailService)
    {
    }

    public function onBookingConfirmed(BookingConfirmedEvent $event): void
    {
        $booking = $event->booking;

        $this->emailService->sendConfirmation($booking->customer->email, $booking->id);
    }
}
