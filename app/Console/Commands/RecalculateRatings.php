<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

/**
 * LESSON: This command recalculates average ratings for all products.
 * Useful if ratings ever get out of sync (e.g. after importing data,
 * or manually deleting reviews in the database).
 *
 * Run: php artisan gymstore:recalculate-ratings
 * Or schedule: Schedule::command('gymstore:recalculate-ratings')->weekly();
 */
class RecalculateRatings extends Command
{
    protected $signature   = 'gymstore:recalculate-ratings';
    protected $description = 'Recalculate average ratings and review counts for all products';

    public function handle(): int
    {
        $products = Product::withCount(['reviews as approved_reviews_count' => function ($q) {
            $q->where('status', 'approved');
        }])->get();

        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        /** @var Product $product */
        foreach ($products as $product) {
            $product->recalculateRating();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Recalculated ratings for {$products->count()} products.");

        return Command::SUCCESS;
    }
}