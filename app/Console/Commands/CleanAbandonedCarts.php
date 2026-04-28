<?php

namespace App\Console\Commands;

use App\Models\Cart;
use Illuminate\Console\Command;

/**
 * LESSON: Artisan Commands are custom CLI commands you run via:
 *   php artisan gymstore:clean-carts
 *
 * You can also schedule them in routes/console.php to run automatically.
 *
 * This command cleans up abandoned guest carts older than 7 days.
 * Without this, the carts table grows forever with abandoned sessions.
 *
 * Schedule in routes/console.php:
 *   Schedule::command('gymstore:clean-carts')->daily();
 */
class CleanAbandonedCarts extends Command
{
    protected $signature   = 'gymstore:clean-carts {--days=7 : Delete carts older than this many days}';
    protected $description = 'Remove abandoned guest carts older than the specified number of days';

    public function handle(): int
    {
        $days    = (int) $this->option('days');
        $cutoff  = now()->subDays($days);

        // Only delete guest carts (session-based), not user carts
        $deleted = Cart::whereNull('user_id')
            ->where('updated_at', '<', $cutoff)
            ->count();

        Cart::whereNull('user_id')
            ->where('updated_at', '<', $cutoff)
            ->delete();

        $this->info("Deleted {$deleted} abandoned cart(s) older than {$days} days.");

        return Command::SUCCESS;
    }
}