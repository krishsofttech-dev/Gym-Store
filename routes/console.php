<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/**
 * LESSON: routes/console.php is where you schedule recurring tasks.
 *
 * Laravel's task scheduler runs via a single cron entry on your server:
 *   * * * * * cd /var/www/gym-store && php artisan schedule:run >> /dev/null 2>&1
 *
 * That single cron fires every minute. Laravel then decides which
 * of your scheduled tasks are due to run at that moment.
 *
 * This is MUCH cleaner than having 10 separate cron entries.
 *
 * To test locally: php artisan schedule:work  (runs every minute in terminal)
 * To see what's scheduled: php artisan schedule:list
 */

// ---- GymStore Scheduled Tasks ----

// Clean abandoned guest carts every day at 2am (low traffic time)
Schedule::command('gymstore:clean-carts --days=7')->dailyAt('02:00');

// Recalculate all product ratings every Sunday night
Schedule::command('gymstore:recalculate-ratings')->weekly()->sundays()->at('03:00');

// Clear expired password reset tokens (built-in Laravel command)
Schedule::command('auth:clear-resets')->everyFifteenMinutes();

// ---- Optional: database backup (requires spatie/laravel-backup) ----
// Schedule::command('backup:run')->dailyAt('01:00');