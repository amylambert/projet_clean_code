<?php

declare(strict_types=1);

final class SmsObserver implements IBookingObserver
{
    public function __construct(private SmsClient $smsClient)
    {
    }

    public function onBookingConfirmed(BookingConfirmedEvent $event): void
    {
        $customer = $event->booking->customer;

        if (!$customer->hasPhoneNumber()) {
            return;
        }

        $this->smsClient->send($customer->phone, "Booking {$event->booking->id} confirmed");
    }
}
