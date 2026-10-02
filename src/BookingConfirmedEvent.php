<?php

declare(strict_types=1);

final class BookingConfirmedEvent
{
    public function __construct(
        public Booking $booking,
        public float $total
    ) {}
}
