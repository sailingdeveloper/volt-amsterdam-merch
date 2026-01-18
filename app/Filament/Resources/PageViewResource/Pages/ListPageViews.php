<?php

/**
 * @author Thijs de Maa <mdemaa@bunq.com>
 *
 * @since 20260118 Initial creation.
 */

namespace App\Filament\Resources\PageViewResource\Pages;

use App\Filament\Resources\PageViewResource;
use Filament\Resources\Pages\ListRecords;

class ListPageViews extends ListRecords
{
    protected static string $resource = PageViewResource::class;

    /**
     * @return array<mixed>
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
