<?php

namespace App\Filament\Resources\CostumerResource\Pages;

use App\Filament\Resources\CostumerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCostumer extends EditRecord
{
    protected static string $resource = CostumerResource::class;

    public function getTitle(): string
    {
        return 'Editar Cliente';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Excluir'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Cliente atualizado com sucesso!';
    }
}