<?php

declare(strict_types=1);

final class BookingServiceFactory
{
    public static function create(): BookingService
    {
        $subject = new BookingConfirmationSubject();

        $subject->attach(new EmailObserver(new EmailService()));
        $subject->attach(new LoyaltyObserver(new LoyaltyService()));
        $subject->attach(new AnalyticsObserver(new AnalyticsClient()));
        $subject->attach(new SmsObserver(new SmsClient()));

        return new BookingService($subject);
    }
}