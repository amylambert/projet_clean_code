<?php

declare(strict_types=1);

final class LoyaltyObserver implements IBookingObserver
{
    private const POINTS_PER_EURO = 1;

    public function __construct(private LoyaltyService $loyaltyService)
    {
    }

    public function onBookingConfirmed(BookingConfirmedEvent $event): void
    {
        $points = (int) floor($event->total) * self::POINTS_PER_EURO;

        $this->loyaltyService->addPoints($event->booking->customer->id, $points);
    }
}
