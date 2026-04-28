<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminReviewController;

/*
|--------------------------------------------------------------------------
| LESSON: How Laravel routing works
|--------------------------------------------------------------------------
|
| Every URL in your app is mapped here. Laravel reads these top-to-bottom.
|
| Route::get('/path', [Controller::class, 'method'])
|   └── HTTP verb (get/post/put/patch/delete)
|   └── URL pattern
|   └── Which controller method handles it
|
| Named routes:  ->name('products.show')
|   Lets you use route('products.show', $product) instead of hardcoded URLs.
|   If you ever change the URL, only this file needs updating.
|
| Route groups:  Route::middleware([...])->prefix('...')->group(function() {...})
|   Apply shared middleware (auth, admin) and URL prefixes to many routes at once.
|
*/

// ============================================================
// PUBLIC ROUTES — no login required
// ============================================================

Route::get('/', [HomeController::class, 'index'])->name('home');

// --- Product catalog ---
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
// LESSON: {product:slug} = "route model binding by slug column"
// Laravel auto-queries Product::where('slug', $slug)->firstOrFail()
// No manual DB query needed in the controller!

// --- Categories ---
Route::get('/category/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');

// --- Search ---
Route::get('/search', [SearchController::class, 'index'])->name('search');

// --- Cart (guests CAN have a cart) ---
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/',                  [CartController::class, 'index'])->name('index');
    Route::post('/add',              [CartController::class, 'add'])->name('add');
    Route::patch('/update/{item}',   [CartController::class, 'update'])->name('update');
    Route::delete('/remove/{item}',  [CartController::class, 'remove'])->name('remove');
    Route::delete('/clear',          [CartController::class, 'clear'])->name('clear');
    // LESSON: prefix('cart') + name('cart.') means:
    //   URL: /cart/add     Name: cart.add
    //   URL: /cart/clear   Name: cart.clear
});

// Coupon (applied from cart page)
Route::post('/coupon/apply',  [CouponController::class, 'apply'])->name('coupon.apply');
Route::delete('/coupon/remove', [CouponController::class, 'remove'])->name('coupon.remove');

// ============================================================
// AUTHENTICATED ROUTES — must be logged in
// ============================================================
/**
 * LESSON: middleware('auth') checks if the user is logged in.
 * If not, they get redirected to /login automatically.
 * middleware('verified') additionally checks email is verified.
 */
Route::middleware(['auth', 'verified'])->group(function () {

    // --- Checkout ---
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/',           [CheckoutController::class, 'index'])->name('index');
        Route::post('/process',   [CheckoutController::class, 'process'])->name('process');
        Route::get('/success/{order}', [CheckoutController::class, 'success'])->name('success');
    });

    // --- Orders (customer view) ---
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/',              [OrderController::class, 'index'])->name('index');
        Route::get('/{order}',       [OrderController::class, 'show'])->name('show');
        Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
    });

    // --- Reviews ---
    Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{review}',          [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // --- Wishlist ---
    Route::post('/wishlist/{product}',   [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::get('/wishlist',              [WishlistController::class, 'index'])->name('wishlist.index');

    // --- User Profile ---
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/',             [ProfileController::class, 'show'])->name('show');
        Route::patch('/',           [ProfileController::class, 'update'])->name('update');
        Route::patch('/password',   [ProfileController::class, 'updatePassword'])->name('password');
        Route::get('/addresses',    [ProfileController::class, 'addresses'])->name('addresses');
        Route::post('/addresses',   [ProfileController::class, 'storeAddress'])->name('addresses.store');
        Route::delete('/addresses/{address}', [ProfileController::class, 'destroyAddress'])->name('addresses.destroy');
    });
});

// ============================================================
// ADMIN ROUTES — must be logged in AND be an admin
// ============================================================
/**
 * LESSON: We stack TWO middleware here:
 *   'auth'   must be logged in
 *   'admin'  must have role = 'admin'  (we'll create this middleware)
 *
 * Route::prefix('admin') means all URLs start with /admin/...
 * Route::name('admin.') means all route names start with admin....
 */
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Products CRUD
        Route::resource('products', AdminProductController::class);
        // LESSON: Route::resource() creates 7 routes in one line:
        //   GET    /admin/products            index   (list all)
        //   GET    /admin/products/create     create  (show form)
        //   POST   /admin/products            store   (save new)
        //   GET    /admin/products/{id}       show    (view one)
        //   GET    /admin/products/{id}/edit  edit    (show edit form)
        //   PUT    /admin/products/{id}       update  (save edits)
        //   DELETE /admin/products/{id}       destroy (delete)

        // Categories CRUD
        Route::resource('categories', AdminCategoryController::class);

        // Orders management
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/',                          [AdminOrderController::class, 'index'])->name('index');
            Route::get('/{order}',                   [AdminOrderController::class, 'show'])->name('show');
            Route::patch('/{order}/status',          [AdminOrderController::class, 'updateStatus'])->name('status');
            Route::patch('/{order}/tracking',        [AdminOrderController::class, 'updateTracking'])->name('tracking');
        });

        // Reviews moderation
        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/',                    [AdminReviewController::class, 'index'])->name('index');
            Route::patch('/{review}/approve',  [AdminReviewController::class, 'approve'])->name('approve');
            Route::patch('/{review}/reject',   [AdminReviewController::class, 'reject'])->name('reject');
            Route::delete('/{review}',         [AdminReviewController::class, 'destroy'])->name('destroy');
        });
    });

// ============================================================
// BREEZE AUTH ROUTES (login, register, forgot password, etc.)
// These are auto-loaded by Breeze — kept here for reference:
//   GET  /login            login form
//   POST /login            authenticate
//   POST /logout           log out
//   GET  /register         register form
//   POST /register         create account
//   GET  /forgot-password  password reset form
// ============================================================
require __DIR__.'/auth.php';