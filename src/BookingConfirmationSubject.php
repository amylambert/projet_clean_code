<?php

declare(strict_types=1);

final class BookingConfirmationSubject
{
    private array $observers = [];

    public function attach(IBookingObserver $observer): void
    {
        $this->observers[] = $observer;
    }

    public function notify(BookingConfirmedEvent $event): void
    {
        foreach ($this->observers as $observer) {
            $this->notifyObserver($observer, $event);
        }
    }

    private function notifyObserver(IBookingObserver $observer, BookingConfirmedEvent $event): void
    {
        try {
            $observer->onBookingConfirmed($event);
        } catch (Throwable $error) {
            echo 'OBSERVER FAILED ' . $observer::class . ': ' . $error->getMessage() . PHP_EOL;
        }
    }
}
