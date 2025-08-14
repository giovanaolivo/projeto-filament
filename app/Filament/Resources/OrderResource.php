<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatusEnum;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use App\Filament\Exports\OrderExporter; 
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Pedidos';

    protected static ?string $modelLabel = 'pedido';
    
    protected static ?string $pluralModelLabel = 'pedidos';

    protected static ?string $label = 'pedido';

    protected static ?string $pluralLabel = 'pedidos';

    protected static ?string $navigationGroup = 'Loja';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', '!=', 'processing')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('status', '!=', 'processing')->count() < 10
            ? 'primary' : 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('order-details')
                        ->label('Detalhes do Pedido')
                        ->schema([
                            TextInput::make('number')
                                ->label('Número')
                                ->default('OR-' . random_int(100000, 9999999))
                                ->disabled()
                                ->dehydrated()
                                ->required(),

                            Select::make('costumer_id')
                                ->label('Cliente')
                                ->relationship('costumer', 'name')
                                ->searchable()
                                ->required(),

                            TextInput::make('shipping_price')
                                ->label('Custo de Entrega')
                                ->dehydrated()
                                ->numeric()
                                ->prefix('R$')
                                ->required(),

                            Select::make('status')
                                ->label('Status')
                                ->options([
                                    'pending' => OrderStatusEnum::PENDING->value,
                                    'processing' => OrderStatusEnum::PROCESSING->value,
                                    'completed' => OrderStatusEnum::COMPLETED->value,
                                    'declined' => OrderStatusEnum::DECLINED->value,
                                ])->required(),

                            MarkdownEditor::make('notes')
                                ->label('Observações')
                                ->columnSpanFull()
                        ])->columns(2),

                    Step::make('order-items')
                        ->label('Itens do Pedido')
                        ->schema([
                            Repeater::make('items')
                                ->label('Itens')
                                ->relationship()
                                ->schema([
                                    Select::make('product_id')
                                        ->label('Produto')
                                        ->options(Product::query()->pluck('name', 'id'))
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(fn($state, Set $set) =>
                                            $set('unit_price', Product::find($state)?->price ?? 0)
                                        ),

                                    TextInput::make('quantity')
                                        ->label('Quantidade')
                                        ->numeric()
                                        ->live()
                                        ->dehydrated()
                                        ->default(1)
                                        ->minValue(1)
                                        ->required(),

                                    TextInput::make('unit_price')
                                        ->label('Preço Unitário')
                                        ->disabled()
                                        ->dehydrated()
                                        ->numeric()
                                        ->prefix('R$')
                                        ->required(),

                                    Placeholder::make('total_price')
                                        ->label('Preço Total')
                                        ->content(function ($get) {
                                            $quantity = $get('quantity') ?? 0;
                                            $unitPrice = $get('unit_price') ?? 0;
                                            $total = $quantity * $unitPrice;
                                            return 'R$ ' . number_format($total, 2, ',', '.');
                                        })
                                ])->columns(4)
                                ->addActionLabel('Adicionar Item')
                                ->deleteAction(fn($action) => $action->label('Remover'))
                        ])
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        // Traduções e cores dos status
        $statusLabels = [
            'pending' => 'Pendente',
            'processing' => 'Processando',
            'completed' => 'Concluído',
            'declined' => 'Recusado',
        ];

        $statusColors = [
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'declined' => 'danger',
        ];

        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('costumer.name')
                    ->label('Cliente')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $statusLabels[$state] ?? ucfirst($state))
                    ->color(fn (string $state): string => $statusColors[$state] ?? 'secondary'),

                TextColumn::make('shipping_price')
                    ->label('Frete')
                    ->money('BRL')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Data do Pedido')
                    ->date('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options($statusLabels)
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Visualizar'),
                    Tables\Actions\EditAction::make()->label('Editar'),
                    Tables\Actions\DeleteAction::make()->label('Excluir'),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Excluir Selecionados'),
                ]),
            ])
            ->emptyStateHeading('Nenhum pedido encontrado')
            ->emptyStateDescription('Comece criando um novo pedido.')
            ->emptyStateIcon('heroicon-o-shopping-bag');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
