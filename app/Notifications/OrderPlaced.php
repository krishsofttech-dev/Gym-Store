<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * LESSON: Laravel Notifications are a clean way to send emails,
 * SMS, Slack messages etc. from one class.
 *
 * To send: $user->notify(new OrderPlaced($order));
 *
 * The toMail() method defines the email content using Laravel's
 * fluent MailMessage builder — no HTML templates needed for simple emails.
 * For rich HTML emails, we use a Blade view via ->view().
 *
 * Notifications are automatically queued if you implement ShouldQueue
 * and have a queue driver configured. For now we use sync (instant send).
 */
class OrderPlaced extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    /**
     * Which channels to send on.
     * Add 'database' here to also store in notifications table.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order->load('items');

        return (new MailMessage)
            ->subject("Order Confirmed — {$order->order_number}")
            ->greeting("Hi {$notifiable->name}!")
            ->line("Your order **{$order->order_number}** has been placed successfully.")
            ->line("**Order Total:** {$order->formatted_total}")
            ->line("**Payment:** " . ucfirst($order->payment_method ?? 'N/A'))
            ->line("**Shipping to:** {$order->shipping_address}")
            ->action('View Your Order', route('orders.show', $order))
            ->line('Thank you for shopping with GymStore!')
            ->salutation('The GymStore Team');
    }
}