<?php

declare(strict_types=1);

final class AnalyticsObserver implements IBookingObserver
{
    private const EVENT_NAME = 'booking_confirmed';

    public function __construct(private AnalyticsClient $analyticsClient)
    {
    }

    public function onBookingConfirmed(BookingConfirmedEvent $event): void
    {
        $this->analyticsClient->track(self::EVENT_NAME, [
            'booking_id' => $event->booking->id,
            'total' => $event->total,
        ]);
    }
}
