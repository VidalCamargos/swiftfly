<?php

namespace Tests\Unit\Listeners\TravelOrder\Updated;

use App\Enums\TravelOrder\Status;
use App\Events\TravelOrder\Updated;
use App\Listeners\TravelOrder\Updated\NotifyStatusChanged;
use App\Models\TravelOrder;
use App\Models\User;
use App\Notifications\TravelOrderStatusChangedNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class NotifyStatusChangedTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();
    }

    #[DataProvider('notifiableStatusDataProvider')]
    public function test_sends_notification_when_status_changes_to_notifiable_status(Status $newStatus, string $expectedStatusText): void
    {
        Notification::fake();

        $travelOrderUser = User::factory()->create([
            'name' => 'John Doe',
        ]);

        $travelOrder = TravelOrder::factory()
            ->for($travelOrderUser)
            ->create(['status' => Status::REQUESTED]);

        $travelOrder->update(['status' => $newStatus]);

        $updatedEvent = new Updated($travelOrder);
        $notifyStatusChangedListener = resolve(NotifyStatusChanged::class);

        $this->assertTrue($notifyStatusChangedListener->shouldQueue($updatedEvent));
        $notifyStatusChangedListener->handle($updatedEvent);

        Notification::assertSentTo(
            $travelOrderUser,
            TravelOrderStatusChangedNotification::class,
            function (TravelOrderStatusChangedNotification $travelOrderStatusChangedNotification) use ($travelOrderUser, $expectedStatusText): bool {
                $mailMessage = $travelOrderStatusChangedNotification->toMail($travelOrderUser);

                $this->assertSame("Seu pedido de viagem foi {$expectedStatusText}", $mailMessage->subject);
                $this->assertSame('Olá John Doe!', $mailMessage->greeting);
                $this->assertSame(
                    "O status do seu pedido de viagem foi alterado para {$expectedStatusText}.",
                    $mailMessage->introLines[0] ?? null,
                );
                $this->assertSame(
                    'Agradecemos sua preferência. Em caso de dúvidas, entre em contato.',
                    $mailMessage->introLines[1] ?? null,
                );

                return true;
            },
        );
    }

    public function test_does_not_send_notification_when_status_does_not_change(): void
    {
        Notification::fake();

        $travelOrderUser = User::factory()->create();

        $travelOrder = TravelOrder::factory()
            ->for($travelOrderUser)
            ->create(['status' => Status::REQUESTED]);

        $travelOrder->update(['destination' => 'New destination']);

        $updatedEvent = new Updated($travelOrder);
        $notifyStatusChangedListener = resolve(NotifyStatusChanged::class);

        $this->assertFalse($notifyStatusChangedListener->shouldQueue($updatedEvent));

        Notification::assertNothingSent();
    }

    public static function notifiableStatusDataProvider(): Iterator
    {
        yield 'approved' => [Status::APPROVED, 'aprovado'];
        yield 'canceled' => [Status::CANCELED, 'cancelado'];
    }
}
