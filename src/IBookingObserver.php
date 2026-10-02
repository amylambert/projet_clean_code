<?php

declare(strict_types=1);

interface BookingObserver
{
    public function update(BookingConfirmedEvent $event): void;
}
