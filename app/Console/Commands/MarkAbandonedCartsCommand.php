<?php

namespace App\Console\Commands;

use App\Models\Cart;
use Illuminate\Console\Command;

class MarkAbandonedCartsCommand extends Command
{
    protected const ABANDONMENT_THRESHOLD_HOURS = 1;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carts:mark-abandoned';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark active carts as abandoned after 1 hour of inactivity.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $threshold = now()->subHours(self::ABANDONMENT_THRESHOLD_HOURS);

        $count = Cart::where('status', 'active')
            ->where('updated_at', '<', $threshold)
            ->update(['status' => 'abandoned']);

        $this->info("Marked {$count} carts as abandoned.");

        return Command::SUCCESS;
    }
}
