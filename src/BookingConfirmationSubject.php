<?php

declare(strict_types=1);

final class BookingConfirmationSubject
{
    private array $observers = [];

    public function attach(BookingObserver $observer): void
    {
        $this->observers[] = $observer;
    }

    public function notify(BookingConfirmedEvent $event): void
    {
        foreach ($this->observers as $observer) {
            $observer->update($event);
        }
    }
}
