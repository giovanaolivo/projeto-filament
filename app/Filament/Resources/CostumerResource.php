<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CostumerResource\Pages;
use App\Filament\Resources\CostumerResource\RelationManagers;
use App\Models\Costumer;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CostumerResource extends Resource
{
    protected static ?string $model = Costumer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Clientes';

    // Adicionar essas propriedades para tradução completa
    protected static ?string $modelLabel = 'cliente';
    
    protected static ?string $pluralModelLabel = 'clientes';

    protected static ?string $label = 'cliente';

    protected static ?string $pluralLabel = 'clientes';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'Loja';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                
                Section::make()
                ->schema([
                    TextInput::make('name')
                    ->label('Nome')
                    ->maxLength(50) // Corrigido: era maxValue, deveria ser maxLength
                    ->required(),

                    TextInput::make('email')
                    ->label('Endereço de Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                    TextInput::make('phone')
                    ->label('Telefone')
                    ->maxLength(50) // Corrigido: era maxValue, deveria ser maxLength
                    ->tel(), // Adicionado tipo telefone

                    DatePicker::make('date_of_birth')
                    ->label('Data de Nascimento')
                    ->native(false), // Para melhor experiência no Brasil

                    TextInput::make('city')
                    ->label('Cidade')
                    ->required(),

                    TextInput::make('zip_code')
                    ->label('CEP')
                    ->mask('99999-999') // Máscara para CEP brasileiro
                    ->required(),

                    TextInput::make('address')
                    ->label('Endereço')
                    ->required()
                    ->columnSpanFull()
                ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            
                TextColumn::make('name')
                ->label('Nome')
                ->searchable()
                ->sortable(),

                TextColumn::make('email')
                ->label('Email')
                ->searchable()
                ->sortable(),

                TextColumn::make('phone')
                ->label('Telefone')
                ->searchable()
                ->sortable(),

                TextColumn::make('city')
                ->label('Cidade')
                ->searchable()
                ->sortable(),

                TextColumn::make('date_of_birth')
                ->label('Data de Nascimento')
                ->date('d/m/Y') // Formato brasileiro
                ->sortable(),

            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                    ->label('Visualizar'),
                    Tables\Actions\EditAction::make()
                    ->label('Editar'),
                    Tables\Actions\DeleteAction::make()
                    ->label('Excluir'),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                    ->label('Excluir Selecionados'),
                ]),
            ])
            ->emptyStateHeading('Nenhum cliente encontrado')
            ->emptyStateDescription('Comece criando um novo cliente.')
            ->emptyStateIcon('heroicon-o-user-group');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCostumers::route('/'),
            'create' => Pages\CreateCostumer::route('/create'),
            'edit' => Pages\EditCostumer::route('/{record}/edit')
        ];
    }
}