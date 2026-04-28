<?php

namespace App\Providers;

use App\Services\CartService;
use App\Services\ImageService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CartService::class,    fn() => new CartService());
        $this->app->singleton(ImageService::class,   fn() => new ImageService());
        $this->app->singleton(PaymentService::class, fn() => new PaymentService());
        $this->app->singleton(OrderService::class, function ($app) {
            return new OrderService($app->make(CartService::class));
        });
    }

    public function boot(): void
    {
        View::composer('*', function ($view) {
            try {
                $cartService = app(CartService::class);

                // Share cart count for navbar badge
                $view->with('globalCartCount', $cartService->getCartCount());

                // FIX: Share full cart object so cart drawer works on every page
                // The drawer does isset($cart) — without this it always shows empty
                $cart = $cartService->getCurrentCart();
                if ($cart) {
                    $cart->load('items.product');
                }
                $view->with('globalCart', $cart);

            } catch (\Exception $e) {
                $view->with('globalCartCount', 0);
                $view->with('globalCart', null);
            }
        });
    }
}