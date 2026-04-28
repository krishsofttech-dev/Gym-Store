<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(public Order $order, public string $previousStatus) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order  = $this->order;
        $status = $order->status_label['label'];

        $message = (new MailMessage)
            ->subject("Order Update — {$order->order_number} is now {$status}")
            ->greeting("Hi {$notifiable->name}!")
            ->line("Your order **{$order->order_number}** status has been updated to **{$status}**.");

        // Add tracking info when shipped
        if ($order->status === Order::STATUS_SHIPPED && $order->tracking_number) {
            $message->line("**Tracking Number:** {$order->tracking_number}")
                    ->line("**Carrier:** {$order->shipping_carrier}");
        }

        if ($order->status === Order::STATUS_DELIVERED) {
            $message->line('Your order has been delivered. Enjoy your new gym gear!')
                    ->action('Leave a Review', route('products.index'));
        } elseif ($order->status === Order::STATUS_CANCELLED) {
            $message->line('If you did not request this cancellation, please contact us.');
        } else {
            $message->action('Track Your Order', route('orders.show', $order));
        }

        return $message->salutation('The GymStore Team');
    }
}