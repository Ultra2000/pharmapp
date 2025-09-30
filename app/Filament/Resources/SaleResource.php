<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Filament\Resources\SaleResource\RelationManagers;
use App\Models\Sale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Actions;
use Illuminate\Support\Facades\Auth;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $modelLabel = 'Vente';
    
    protected static ?string $pluralModelLabel = 'Ventes';
    
    protected static ?string $navigationGroup = 'Ventes';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => Auth::id()),

                Forms\Components\Section::make('Produits')
                    ->description('Scanner ou sélectionner les produits à vendre')
                    ->columnSpan('full')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->live()
                            ->schema([
                                Forms\Components\TextInput::make('barcode')
                                    ->label('Code-barres')
                                    ->placeholder('Scanner ou saisir le code-barres')
                                    ->suffixAction(
                                        Action::make('rechercherProduit')
                                            ->icon('heroicon-m-magnifying-glass')
                                            ->label('Rechercher')
                                            ->action(function ($set, $get) {
                                                $barcode = $get('barcode');
                                                $product = \App\Models\Product::where('barcode', $barcode)->first();
                                                
                                                if ($product) {
                                                    $set('product_id', $product->id);
                                                    $set('unit_price', $product->sale_price);
                                                    $quantity = $get('quantity') ?: 1;
                                                    $set('total_price', $quantity * $product->sale_price);
                                                }
                                            })
                                    )
                                    ->columnSpan(2),

                                Forms\Components\Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->label('Produit')
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($state) {
                                            $product = \App\Models\Product::find($state);
                                            if ($product) {
                                                $quantity = $get('quantity') ?: 1;
                                                $set('unit_price', $product->sale_price);
                                                $set('total_price', $quantity * $product->sale_price);
                                            }
                                        } else {
                                            $set('unit_price', 0);
                                            $set('total_price', 0);
                                        }
                                    })
                                    ->columnSpan(5),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Quantité')
                                    ->required()
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(0)
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $unitPrice = $get('unit_price') ?: 0;
                                        $quantity = $state ?: 0;
                                        $set('total_price', $quantity * $unitPrice);
                                    })
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Prix unitaire')
                                    ->required()
                                    ->numeric()
                                    ->prefix('€')
                                    ->readOnly()
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('total_price')
                                    ->label('Total ligne')
                                    ->required()
                                    ->numeric()
                                    ->prefix('€')
                                    ->readOnly()
                                    ->columnSpan(3),
                            ])
                            ->defaultItems(1)
                            ->columns(12)
                            ->itemLabel('Produit')
                            ->addActionLabel('Ajouter un produit')
                            ->reorderableWithButtons()
                            ->deletable(true)
                            ->cloneable(false)
                            ->live()
                    ]),

                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Placeholder::make('total_amount_display')
                            ->label('Montant total')
                            ->content(function ($get) {
                                return collect($get('items') ?? [])->sum('total_price') . ' €';
                            }),
                            
                        Forms\Components\Hidden::make('total_amount')
                            ->dehydrateStateUsing(function ($get) {
                                return collect($get('items') ?? [])->sum('total_price');
                            }),

                        Forms\Components\DateTimePicker::make('date')
                            ->label('Date et heure')
                            ->required()
                            ->default(now()),

                        Forms\Components\FileUpload::make('prescription_file')
                            ->label('Ordonnance')
                            ->image()
                            ->directory('prescriptions')
                            ->preserveFilenames()
                            ->maxSize(5120)
                            ->helperText('Formats acceptés : PDF et images. Taille maximale : 5 Mo')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                    ])
                    ->columns(1)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Nombre de produits'),
                
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Montant total')
                    ->money('eur')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Vendeur')
                    ->sortable(),
                    
                Tables\Columns\ImageColumn::make('prescription_file')
                    ->label('Ordonnance'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Modifier'),
                Tables\Actions\Action::make('download_invoice')
                    ->label('Télécharger la facture')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (Sale $record) => route('generate.invoice', ['sale' => $record]))
                    ->openUrlInNewTab()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Supprimer la sélection'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
