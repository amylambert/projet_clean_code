<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/TestRunner.php';

final class SpyObserver implements IBookingObserver
{
    public int $callCount = 0;

    public function onBookingConfirmed(BookingConfirmedEvent $event): void
    {
        $this->callCount++;
    }
}

final class FailingObserver implements IBookingObserver
{
    public function onBookingConfirmed(BookingConfirmedEvent $event): void
    {
        throw new RuntimeException('boom');
    }
}

function createBooking(
    string $customerType = 'standard',
    string $passType = 'day',
    float $price = 50.0,
    int $quantity = 1,
    ?string $phone = '0600000000'
): Booking {
    $customer = new Customer(1, 'test@example.com', $phone, $customerType);
    $ticket = new Ticket('TEST', 'Ticket test', $price);
    $booking = new Booking(1, $customer, $passType);
    $booking->addItem(new BookingItem($ticket, $quantity));
    return $booking;
}

function createEvent(Booking $booking, float $total = 100.0): BookingConfirmedEvent
{
    return new BookingConfirmedEvent($booking, $total);
}

function createServiceWith(IBookingObserver ...$observers): BookingService
{
    $subject = new BookingConfirmationSubject();

    foreach ($observers as $observer) {
        $subject->attach($observer);
    }

    return new BookingService($subject);
}

/**
 * Exécute une action en capturant son affichage et l'éventuelle exception.
 * La capture est limitée à l'action : les lignes OK / FAIL restent visibles.
 *
 * @return array{result:mixed,error:?Throwable,output:string}
 */
function run(callable $action): array
{
    $result = null;
    $error = null;

    ob_start();
    try {
        $result = $action();
    } catch (Throwable $caught) {
        $error = $caught;
    }
    $output = (string) ob_get_clean();

    return ['result' => $result, 'error' => $error, 'output' => $output];
}

/** Premier mot de chaque ligne affichée : PAYMENT, SQL, EMAIL... */
function outputPrefixes(string $output): array
{
    $lines = array_filter(explode(PHP_EOL, $output), fn (string $line): bool => $line !== '');

    return array_values(array_map(fn (string $line): string => explode(' ', $line)[0], $lines));
}

$tests = new TestRunner();

// ---------------------------------------------------------------------------
// 1. Calcul du total (comportement existant)
// ---------------------------------------------------------------------------

$service = BookingServiceFactory::create();

$standard = createBooking('standard', 'day', 50.0, 2);
$result = run(fn () => $service->confirm($standard, 'stripe'));
$tests->near(100.0, $result['result'], 'standard customer keeps initial total');
$tests->same('confirmed', $standard->status, 'booking becomes confirmed');

$result = run(fn () => $service->confirm(createBooking('vip', 'day', 50.0, 2), 'stripe'));
$tests->near(90.0, $result['result'], 'VIP customer with a total of 100 gets 10 percent discount');

// Mis à jour avec le ticket #102 : la réduction du Pass 3 jours est passée de 10 à 20 euros
$result = run(fn () => $service->confirm(createBooking('standard', '3days', 60.0, 2), 'stripe'));
$tests->near(100.0, $result['result'], 'three day pass discount is 20 euros');

// ---------------------------------------------------------------------------
// 2. Ticket #104 : câblage par défaut (BookingServiceFactory)
// ---------------------------------------------------------------------------
// La ligne "Payment processed in ... seconds" (durée du paiement) s'affiche entre PAYMENT et SQL.
// Sa valeur change à chaque exécution : on ne vérifie que son premier mot ("Payment").

$result = run(fn () => $service->confirm(createBooking('standard', 'day', 50.0, 2), 'stripe'));
$tests->same(
    ['PAYMENT', 'Payment', 'SQL', 'EMAIL', 'LOYALTY', 'ANALYTICS', 'SMS'],
    outputPrefixes($result['output']),
    'default wiring: the four reactions run after SQL INSERT, email first'
);

$result = run(fn () => $service->confirm(createBooking('standard', 'day', 50.0, 2, null), 'stripe'));
$tests->same(
    ['PAYMENT', 'Payment', 'SQL', 'EMAIL', 'LOYALTY', 'ANALYTICS'],
    outputPrefixes($result['output']),
    'default wiring: no SMS when the customer has no phone number'
);

// ---------------------------------------------------------------------------
// 3. Ticket #104 : chaque observateur isolé
// ---------------------------------------------------------------------------

$result = run(fn () => (new EmailObserver(new EmailService()))->onBookingConfirmed(createEvent(createBooking())));
$tests->same('EMAIL test@example.com: booking 1 confirmed' . PHP_EOL, $result['output'], 'EmailObserver sends the confirmation email');

$result = run(fn () => (new LoyaltyObserver(new LoyaltyService()))->onBookingConfirmed(createEvent(createBooking(), 100.0)));
$tests->same('LOYALTY customer=1 points=100' . PHP_EOL, $result['output'], 'LoyaltyObserver gives 1 point per euro');

$result = run(fn () => (new LoyaltyObserver(new LoyaltyService()))->onBookingConfirmed(createEvent(createBooking(), 99.99)));
$tests->same('LOYALTY customer=1 points=99' . PHP_EOL, $result['output'], 'LoyaltyObserver ignores the cents');

$result = run(fn () => (new AnalyticsObserver(new AnalyticsClient()))->onBookingConfirmed(createEvent(createBooking())));
$tests->same(
    true,
    str_starts_with($result['output'], 'ANALYTICS booking_confirmed {"booking_id":1,'),
    'AnalyticsObserver tracks the booking_confirmed event with the booking id'
);

$result = run(fn () => (new SmsObserver(new SmsClient()))->onBookingConfirmed(createEvent(createBooking())));
$tests->same('SMS 0600000000: Booking 1 confirmed' . PHP_EOL, $result['output'], 'SmsObserver sends an SMS when the customer has a phone number');

$result = run(fn () => (new SmsObserver(new SmsClient()))->onBookingConfirmed(createEvent(createBooking('standard', 'day', 50.0, 1, null))));
$tests->same('', $result['output'], 'SmsObserver sends nothing when the phone number is null');

$result = run(fn () => (new SmsObserver(new SmsClient()))->onBookingConfirmed(createEvent(createBooking('standard', 'day', 50.0, 1, ''))));
$tests->same('', $result['output'], 'SmsObserver sends nothing when the phone number is empty');

// ---------------------------------------------------------------------------
// 4. Ticket #104 : contrainte "une nouvelle réaction ne réécrit pas la validation"
// ---------------------------------------------------------------------------

$spy = new SpyObserver();
$result = run(fn () => createServiceWith($spy)->confirm(createBooking('standard', 'day', 50.0, 2), 'stripe'));
$tests->same(1, $spy->callCount, 'a new observer is notified without any change in BookingService');
$tests->same(['PAYMENT', 'Payment', 'SQL'], outputPrefixes($result['output']), 'BookingService no longer sends the email itself');

$spy = new SpyObserver();
$result = run(fn () => createServiceWith($spy)->confirm(createBooking(), 'paypal'));
$tests->same('Invalid payment method.', $result['error']?->getMessage(), 'unknown payment method is still rejected');
$tests->same(0, $spy->callCount, 'observers are not notified when the payment fails');

$spy = new SpyObserver();
$booking = createBooking('standard', 'day', 50.0, 2);
$result = run(fn () => createServiceWith(new FailingObserver(), $spy)->confirm($booking, 'stripe'));
$tests->same(null, $result['error'], 'a failing observer does not make confirm() fail');
$tests->near(100.0, $result['result'], 'confirm() still returns the total when an observer fails');
$tests->same('confirmed', $booking->status, 'the booking stays confirmed when an observer fails');
$tests->same(1, $spy->callCount, 'observers after a failing one are still notified');
$tests->same(true, str_contains($result['output'], 'OBSERVER FAILED FailingObserver: boom'), 'the observer failure is reported');

// ---------------------------------------------------------------------------
// 5. Tickets #103 + #104 ensemble : un paiement PayFast déclenche aussi les observateurs
// ---------------------------------------------------------------------------

$spy = new SpyObserver();
$booking = createBooking('standard', 'day', 50.0, 2);
$result = run(fn () => createServiceWith($spy)->confirm($booking, 'payfast'));
$tests->near(100.0, $result['result'], 'payfast payment returns the same total as stripe');
$tests->same('confirmed', $booking->status, 'booking is confirmed after a payfast payment');
$tests->same(1, $spy->callCount, 'observers are notified after a payfast payment');
$tests->same(true, str_contains($result['output'], 'PAYMENT payfast_pf_'), 'payfast transaction id is printed');

$tests->summary();
