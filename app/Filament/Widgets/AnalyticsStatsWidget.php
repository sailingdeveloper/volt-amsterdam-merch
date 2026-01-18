<?php

/**
 * @author Thijs de Maa <mdemaa@bunq.com>
 *
 * @since 20260118 Initial creation.
 */

namespace App\Filament\Widgets;

use App\Models\PageView;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AnalyticsStatsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    /**
     * @return Stat[]
     */
    protected function getStats(): array
    {
        $totalViews = PageView::count();
        $totalUnique = PageView::distinct('session_id')->count('session_id');
        $viewsToday = PageView::whereDate('created_at', today())->count();
        $uniqueToday = PageView::whereDate('created_at', today())->distinct('session_id')->count('session_id');

        return [
            Stat::make('Total Page Views', number_format($totalViews))
                ->description('All time')
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),
            Stat::make('Unique Visitors', number_format($totalUnique))
                ->description('All time')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            Stat::make('Views Today', number_format($viewsToday))
                ->description('Page views today')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),
            Stat::make('Visitors Today', number_format($uniqueToday))
                ->description('Unique visitors today')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('warning'),
        ];
    }
}
