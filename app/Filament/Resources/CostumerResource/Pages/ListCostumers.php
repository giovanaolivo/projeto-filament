<?php

namespace App\Filament\Resources\CostumerResource\Pages;

use App\Filament\Resources\CostumerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCostumers extends ListRecords
{
    protected static string $resource = CostumerResource::class;

    public function getTitle(): string
    {
        return 'Clientes';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Criar Cliente')
                ->icon('heroicon-m-plus'),
        ];
    }
}