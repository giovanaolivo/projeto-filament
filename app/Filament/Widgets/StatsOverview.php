<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatusEnum;
use App\Models\Costumer;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget

{

    protected static ?string $pollingInterval = '15s';

    protected static ?int $sort = 2;

    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        return [

            Stat::make('Total de Clientes' , Costumer::count())
            ->description('Aumento no número de clientes')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('success')
            ->chart([7, 3, 4, 5, 6, 3, 5, 3]),

            Stat::make('Total de Produtos', Product::count())
            ->description('Total de produtos no sistema')
            ->descriptionIcon('heroicon-m-arrow-trending-down')
            ->color('danger')
            ->chart([7, 3, 4, 5, 6, 3, 5, 3]),

            Stat::make('Pedidos Pendentes', Order::where('status', OrderStatusEnum::PENDING->value)->count())
            ->description('Pedidos aguardando processamento')
            ->descriptionIcon('heroicon-m-arrow-trending-down')
            ->color('danger')
            ->chart([7, 3, 4, 5, 6, 3, 5, 3]),

        ];
    }
}