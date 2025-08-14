<?php

namespace App\Filament\Resources\CostumerResource\Pages;

use App\Filament\Resources\CostumerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCostumer extends CreateRecord
{
    protected static string $resource = CostumerResource::class;

    public function getTitle(): string
    {
        return 'Criar Cliente';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Cliente criado com sucesso!';
    }
}