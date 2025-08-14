<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class ProductsChart extends ChartWidget
{

    protected static ?int $sort = 3;

    protected static ?string $heading = 'Gráfico de Produtos';

    protected function getData(): array
    {
        $data = $this->getProductsPerMonth();

        return [
            'datasets' => [
                [
                    'label' => 'Produtos criados',
                    'data' => $data['productsPerMonth'],
                ]
            ],
            'labels' => $data['months'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function getProductsPerMonth(): array
    {
        $now = Carbon::now();
        $productsPerMonth = [];

        $months = collect(range(1, 12))->map(function ($month) use ($now, &$productsPerMonth) {
            $count = Product::whereMonth('created_at', $month)->count();
            $productsPerMonth[] = $count;

            return Carbon::createFromDate($now->year, $month, 1)->format('M');
        })->toArray();

        return [
            'productsPerMonth' => $productsPerMonth,
            'months' => $months,
        ];
    }
}