<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\NotificationSetting;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Shared plumbing for the four booking lifecycle notifications. Which
 * channels actually fire is decided per-event by the admin-configured
 * NotificationSetting row, not hardcoded here.
 */
abstract class BookingNotification extends Notification
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
        return NotificationSetting::forEvent($this->eventType())?->activeChannels() ?? ['database'];
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
            ->action('View Booking', route('admin.bookings.edit', $this->booking))
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
            'url' => route('admin.bookings.edit', $this->booking),
        ];
    }
}
