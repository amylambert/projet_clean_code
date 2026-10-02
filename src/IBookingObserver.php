<?php

declare(strict_types=1);

interface IBookingObserver
{
    public function onBookingConfirmed(BookingConfirmedEvent $event): void;
}
