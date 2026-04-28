<?php

namespace App\Services;

use App\Models\Order;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

/**
 * LESSON: PaymentService wraps Stripe so the rest of your app
 * never imports Stripe directly. If you ever switch payment providers,
 * you only change this one file.
 *
 * This is called the "Adapter" pattern — your app talks to
 * PaymentService, which translates to/from Stripe's API.
 *
 * Setup:
 *   1. Add STRIPE_KEY and STRIPE_SECRET to .env
 *   2. composer require stripe/stripe-php (already done in Step 1)
 */
class PaymentService
{
    private ?StripeClient $stripe;

    public function __construct()
    {
        $secret = config('services.stripe.secret');

        // LESSON: Graceful degradation — if Stripe isn't configured,
        // don't crash. COD still works without a Stripe key.
        $this->stripe = $secret ? new StripeClient($secret) : null;
    }

    // =========================================================
    // CREATE PAYMENT INTENT
    // =========================================================

    /**
     * Create a Stripe PaymentIntent for the given amount.
     * The client secret is returned to the frontend so Stripe.js
     * can confirm the payment securely in the browser.
     *
     * LESSON: PaymentIntents are Stripe's recommended flow.
     * The amount is in the SMALLEST currency unit (cents/paise).
     * Rs. 1,500 = 150000 (LKR has no sub-unit, but Stripe still
     * requires the integer format).
     */
    public function createPaymentIntent(float $amount, string $currency = 'lkr'): array
    {
        if (! $this->stripe) {
            return ['success' => false, 'message' => 'Payment gateway not configured.'];
        }

        try {
            $intent = $this->stripe->paymentIntents->create([
                'amount'   => (int) round($amount * 100), // convert to smallest unit
                'currency' => $currency,
                'automatic_payment_methods' => ['enabled' => true],
            ]);

            return [
                'success'       => true,
                'client_secret' => $intent->client_secret,
                'intent_id'     => $intent->id,
            ];
        } catch (ApiErrorException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // =========================================================
    // VERIFY PAYMENT
    // =========================================================

    /**
     * Retrieve a PaymentIntent from Stripe and verify its status.
     * Called after checkout to confirm payment succeeded.
     */
    public function verifyPayment(string $paymentIntentId): array
    {
        if (! $this->stripe) {
            return ['success' => false, 'message' => 'Payment gateway not configured.'];
        }

        try {
            $intent = $this->stripe->paymentIntents->retrieve($paymentIntentId);

            return [
                'success' => $intent->status === 'succeeded',
                'status'  => $intent->status,
                'amount'  => $intent->amount / 100, // convert back from smallest unit
            ];
        } catch (ApiErrorException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // =========================================================
    // REFUND
    // =========================================================

    /**
     * Issue a full refund for an order.
     * Called from admin when processing a return/refund.
     */
    public function refundOrder(Order $order): array
    {
        if (! $this->stripe) {
            return ['success' => false, 'message' => 'Payment gateway not configured.'];
        }

        if (! $order->stripe_payment_intent_id) {
            return ['success' => false, 'message' => 'No payment intent on this order.'];
        }

        try {
            $refund = $this->stripe->refunds->create([
                'payment_intent' => $order->stripe_payment_intent_id,
            ]);

            if ($refund->status === 'succeeded') {
                $order->update(['payment_status' => Order::PAYMENT_REFUNDED]);
                $order->transitionTo(Order::STATUS_REFUNDED);
            }

            return [
                'success' => $refund->status === 'succeeded',
                'refund_id' => $refund->id,
            ];
        } catch (ApiErrorException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // =========================================================
    // WEBHOOK HANDLER
    // =========================================================

    /**
     * Verify and process a Stripe webhook payload.
     * LESSON: Webhooks are Stripe calling YOUR server when something
     * happens — payment succeeded, refund processed, etc.
     * We verify the signature so only real Stripe calls get processed.
     */
    public function handleWebhook(string $payload, string $sigHeader): array
    {
        if (! $this->stripe) {
            return ['success' => false];
        }

        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sigHeader, $webhookSecret
            );

            return match($event->type) {
                'payment_intent.succeeded' => $this->handlePaymentSucceeded($event->data->object),
                'payment_intent.payment_failed' => $this->handlePaymentFailed($event->data->object),
                default => ['success' => true, 'message' => 'Event ignored'],
            };
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function handlePaymentSucceeded(object $intent): array
    {
        $order = Order::where('stripe_payment_intent_id', $intent->id)->first();

        if ($order && ! $order->isPaid()) {
            $order->update(['payment_status' => Order::PAYMENT_PAID]);
            $order->transitionTo(Order::STATUS_CONFIRMED);
        }

        return ['success' => true];
    }

    private function handlePaymentFailed(object $intent): array
    {
        $order = Order::where('stripe_payment_intent_id', $intent->id)->first();
        $order?->transitionTo(Order::STATUS_CANCELLED);

        return ['success' => true];
    }

    public function isConfigured(): bool
    {
        return $this->stripe !== null;
    }
}