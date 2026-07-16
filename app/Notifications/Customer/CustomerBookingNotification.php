<?php

namespace App\Notifications\Customer;

use App\Models\Booking;
use App\Models\NotificationSetting;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Shared plumbing for customer-facing booking lifecycle notifications —
 * the customer-side counterpart to App\Notifications\BookingNotification.
 * Channels are decided by the same admin-configured NotificationSetting row
 * used for the admin notifications of the same event, so one toggle covers
 * both audiences.
 */
abstract class CustomerBookingNotification extends Notification
{
    public function __construct(public Booking $booking)
    {
    }

    abstract public function eventType(): string;

    abstract protected function title(): string;

    abstract protected function message(): string;

    abstract protected function mailSubject(): string;

    /**
     * @return array<int, string>
     */
    abstract protected function mailLines(): array;

    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        $channels = NotificationSetting::forEvent($this->eventType())?->activeChannels() ?? ['database'];

        if (! $notifiable->email_notifications_enabled) {
            $channels = array_diff($channels, ['mail']);
        }

        return array_values($channels);
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->mailSubject())
            ->greeting('Hello '.$notifiable->name.',');

        foreach ($this->mailLines() as $line) {
            $mail->line($line);
        }

        return $mail
            ->action('View Booking', route('customer.bookings.show', $this->booking))
            ->salutation('— '.setting('company_name', config('app.name')));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'event_type' => $this->eventType(),
            'title' => $this->title(),
            'message' => $this->message(),
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
            'url' => route('customer.bookings.show', $this->booking),
        ];
    }
}
