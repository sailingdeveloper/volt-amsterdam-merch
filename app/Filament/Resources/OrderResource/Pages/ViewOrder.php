<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cancel')
                ->label(__('shop.cancel_order'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading(__('shop.cancel_order'))
                ->modalDescription(__('shop.cancel_order_confirm'))
                ->action(fn () => $this->record->update(['status' => 'canceled']))
                ->hidden(fn (): bool => $this->record->status === 'canceled'),
        ];
    }
}
