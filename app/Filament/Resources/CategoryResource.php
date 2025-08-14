<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\RelationManagers\ProductsRelationManager;
use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use App\Models\Product;
use Dom\Text;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Categorias';

    // Adicionar essas propriedades para tradução completa
    protected static ?string $modelLabel = 'categoria';
    
    protected static ?string $pluralModelLabel = 'categorias';

    protected static ?string $label = 'categoria';

    protected static ?string $pluralLabel = 'categorias';

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
                        // Corrigido: deveria ser Category::class, não Product::class
                        ->unique(Category::class, 'slug', ignoreRecord:true),

                        MarkdownEditor::make('description')
                        ->label('Descrição')
                        ->columnSpanFull()
                    ])->columns(2)
                 ]),

                 Group::make()
                 ->schema([
                    Section::make('Status')
                    ->schema([
                        Toggle::make('is_visible')
                        ->label('Visibilidade')
                        ->helperText('Ativar ou desativar a visibilidade da categoria')
                        ->default(true),

                        Select::make('parent_id')
                        ->label('Categoria Pai')
                        ->relationship('parent', 'name')
                        ->searchable()
                        ->preload() // Melhor performance

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
                ->sortable()
                ->searchable(),

                TextColumn::make('parent.name')
                ->label('Categoria Pai')
                ->searchable()
                ->sortable()
                ->placeholder('Categoria Principal'), // Texto quando não há categoria pai

                IconColumn::make('is_visible')
                ->label('Visibilidade')
                ->boolean()
                ->sortable(),

                TextColumn::make('updated_at')
                ->date('d/m/Y') // Formato brasileiro
                ->label('Data de Atualização')
                ->sortable(),

            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_visible')
                ->label('Visibilidade')
                ->boolean()
                ->trueLabel('Apenas Categorias Visíveis')
                ->falseLabel('Apenas Categorias Ocultas')
                ->native(false),

                Tables\Filters\SelectFilter::make('parent_id')
                ->label('Categoria Pai')
                ->relationship('parent', 'name')
                ->placeholder('Todas as categorias')
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
            ->emptyStateHeading('Nenhuma categoria encontrada')
            ->emptyStateDescription('Comece criando uma nova categoria.')
            ->emptyStateIcon('heroicon-o-tag');
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}