<?php

/**
 * @author Thijs de Maa <mdemaa@bunq.com>
 *
 * @since 20260118 Initial creation.
 */

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    /**
     * @return Stat[]
     */
    protected function getStats(): array
    {
        $orderPaidTotal = Order::where('status', 'paid')->count();
        $revenueTotalCent = Order::where('status', 'paid')->sum('subtotal');
        $orderTodayTotal = Order::where('status', 'paid')
            ->whereDate('created_at', today())
            ->count();
        $revenueTodayCent = Order::where('status', 'paid')
            ->whereDate('created_at', today())
            ->sum('subtotal');

        return [
            Stat::make('Total Orders', $orderPaidTotal)
                ->description('All paid orders')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success'),
            Stat::make('Total Revenue', '€ ' . number_format($revenueTotalCent / 100, 2, ',', '.'))
                ->description('All time revenue')
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success'),
            Stat::make('Orders Today', $orderTodayTotal)
                ->description('Paid orders today')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),
            Stat::make('Revenue Today', '€ ' . number_format($revenueTodayCent / 100, 2, ',', '.'))
                ->description('Revenue today')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
        ];
    }
}
