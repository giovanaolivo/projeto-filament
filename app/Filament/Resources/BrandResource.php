<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Filament\Resources\BrandResource\RelationManagers;
use App\Filament\Resources\BrandResource\RelationManagers\ProductsRelationManager;
use App\Models\Brand;
use Doctrine\DBAL\Schema\View;
use Dom\Text;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ViewAction as ActionsViewAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Marcas';

    // Adicionar essas propriedades para tradução completa
    protected static ?string $modelLabel = 'marca';
    
    protected static ?string $pluralModelLabel = 'marcas';

    protected static ?string $label = 'marca';

    protected static ?string $pluralLabel = 'marcas';

    protected static ?string $navigationGroup = 'Loja';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                
                Group::make()
                ->schema([
                    Section::make([
                        TextInput::make('name')
                        ->label('Nome')
                        ->required()
                     
                        ->live(onBlur: true)
                        ->unique()
                        ->afterStateUpdated(function(string $operation, $state, Forms\Set $set) {
                            if($operation !== 'create') {
                               return;
                            }
    
                            $set('slug', str()->slug($state));
    
                        }),

                        TextInput::make('slug')
                        ->label('Slug')
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->unique(),

                        TextInput::make('url')
                        ->label('URL do Website')
                        ->required()
                        ->unique()
                        ->columnSpan('full'),

                        MarkdownEditor::make('description')
                        ->label('Descrição')
                        ->columnSpan('full')
                    ])->columns(2)
                    ]),

                    Group::make()
                    ->schema([

                        Section::make('Status')
                        ->schema([

                                Toggle::make('is_visible')

                                ->label('Visibilidade')
                                ->helperText('Ativar ou desativar a visibilidade da marca')
                                ->default(true),
                            ]),
                            
                            Group::make()
                            ->schema([
                                Section::make('Cor')
                                ->schema([
                                    ColorPicker::make('primary_hex')
                                    ->label('Cor Primária')

                                    ])
                            ])

                        ])
                        
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

                    TextColumn::make('url')
                    ->label('URL do Website')
                    ->sortable()
                    ->searchable(),

                    ColorColumn::make('primary_hex')
                    ->label('Cor Primária'),

                    IconColumn::make('is_visible')
                    ->boolean()
                    ->sortable()
                    ->label('Visibilidade'),

                    TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->date()
                    ->sortable(),

            ])
            ->filters([
                //
            ])
            ->actions([

                Tables\Actions\ViewAction::make()
                ->label('Visualizar'),
                Tables\Actions\EditAction::make()
                ->label('Editar'),
                Tables\Actions\DeleteAction::make()
                ->label('Excluir'),

                ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                    ->label('Excluir Selecionados'),
                ]),
            ])
            ->emptyStateHeading('Nenhuma marca encontrada')
            ->emptyStateDescription('Comece criando uma nova marca.')
            ->emptyStateIcon('heroicon-o-rectangle-stack');
    }

    public static function getRelations(): array
    {
        return [
            
            ProductsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}