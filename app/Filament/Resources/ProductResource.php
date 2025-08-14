<?php

namespace App\Filament\Resources;

use App\Enums\ProductTypeEnum;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Tables\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Markdown;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use PhpParser\Node\Expr\Ternary;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationLabel = 'Produtos';

    // Adicionar essas propriedades para tradução completa
    protected static ?string $modelLabel = 'produto';
    
    protected static ?string $pluralModelLabel = 'produtos';

    protected static ?string $label = 'produto';

    protected static ?string $pluralLabel = 'produtos';

    protected static ?int $navigationSort = 0;

    protected static ?string $navigationGroup = 'Loja';

    protected static ?string $recordTitleAttribute = 'name';

    protected static int $globalSearchResultsLimit = 20;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug', 'description'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {

        return [
            'Marca' => $record->brand->name,
        ]; 
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['brand']);
    }


    public static function form(Form $form): Form
    {
        return $form

            ->schema([
               Group::make()
               ->schema([
                Section::make() 
                ->schema([
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
                    ->unique(Product::class, 'slug', ignoreRecord:true),

                    MarkdownEditor::make('description')
                    ->label('Descrição')
                    ->columnSpan('full')
                ])->columns(2),

                Section::make('Preço e Estoque') 
                ->schema([

                    TextInput::make('sku')
                    ->label('SKU (Código do Produto)')
                    ->unique()
                    ->required(),


                    TextInput::make('price')
                    ->label('Preço')
                    ->numeric()
                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                    ->required(),

                    TextInput::make('quantity')
                    ->label('Quantidade')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->required(),

                    Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'downloadable' => ProductTypeEnum::DOWNLOADABLE->value,
                        'deliverable' => ProductTypeEnum::DELIVERABLE->value,
                    ])->required()
                ])->columns(2),

                ]),
                Group::make()
                ->schema([
                 Section::make('Status') 
                 ->schema([

                    Toggle::make('is_visible')
                    ->label('Visibilidade')
                    ->helperText('Ativar ou desativar a visibilidade do produto')
                    ->default(true),

                    Toggle::make('is_featured')
                    ->label('Em Destaque')
                    ->helperText('Ativar ou desativar o status de produto em destaque'),

                    DatePicker::make('published_at')
                    ->label('Disponibilidade')
                    ->default(now())
                 ]),

                 Section::make('Imagem')
                 ->schema([
                    FileUpload::make('image')
                    ->label('Imagem')
                    ->directory('form-attachments')
                    ->preserveFilenames()
                    ->image()
                    ->imageEditor(),
                 ])->collapsible(),

                 Section::make('Associações')
                 ->schema([
                    Select::make('brand_id')
                    ->label('Marca')
                    ->relationship('brand', 'name')
                    ->required(),

                Select::make('categories')
                    ->label('Categorias')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->required(),
                 ])
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                
                ImageColumn::make('image')
                ->label('Imagem'),

                TextColumn::make('name')
                ->label('Nome')
                ->searchable()
                ->sortable(),

                TextColumn::make('brand.name')
                ->label('Marca')
                ->searchable()
                ->sortable()
                ->toggleable(),

                IconColumn::make('is_visible')
                ->boolean()
                ->sortable()
                ->toggleable()
                ->label('Visibilidade'),

                TextColumn::make('price')
                ->label('Preço')
                ->sortable()
                ->toggleable(),

                TextColumn::make('quantity')
                ->label('Quantidade')
                ->sortable()
                ->toggleable(),

                TextColumn::make('published_at')
                ->label('Publicado em')
                ->date()
                ->sortable(),

                TextColumn::make('type')
                ->label('Tipo'),

            ])
            ->filters([
                TernaryFilter::make('is_visible')
                ->label('Visibilidade')
                ->boolean()
                ->trueLabel('Apenas Produtos Visíveis')
                ->falseLabel('Apenas Produtos Ocultos')
                ->native(false),

                SelectFilter::make('brand')
                ->label('Marca')
                ->relationship('brand', 'name')

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
            ->emptyStateHeading('Nenhum produto encontrado')
            ->emptyStateDescription('Comece criando um novo produto.')
            ->emptyStateIcon('heroicon-o-rectangle-stack');
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}