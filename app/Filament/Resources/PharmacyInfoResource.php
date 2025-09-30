<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PharmacyInfoResource\Pages;
use App\Models\PharmacyInfo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PharmacyInfoResource extends Resource
{
    protected static ?string $model = PharmacyInfo::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    
    protected static ?string $modelLabel = 'Information pharmacie';
    
    protected static ?string $pluralModelLabel = 'Informations pharmacie';
    
    protected static ?string $navigationGroup = 'Paramètres';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations générales')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom de la pharmacie')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('address')
                            ->label('Adresse')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('zip')
                                    ->label('Code postal')
                                    ->required()
                                    ->maxLength(5),

                                Forms\Components\TextInput::make('city')
                                    ->label('Ville')
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Forms\Components\TextInput::make('phone')
                            ->label('Téléphone')
                            ->tel()
                            ->required()
                            ->maxLength(20),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                    ]),

                Forms\Components\Section::make('Informations légales')
                    ->schema([
                        Forms\Components\TextInput::make('siret')
                            ->label('Numéro SIRET')
                            ->required()
                            ->maxLength(14),

                        Forms\Components\TextInput::make('vat_number')
                            ->label('Numéro de TVA')
                            ->maxLength(13),

                        Forms\Components\TextInput::make('license_number')
                            ->label('Numéro de licence')
                            ->required()
                            ->maxLength(255),
                    ]),

                Forms\Components\Section::make('Options de facturation')
                    ->schema([
                        Forms\Components\FileUpload::make('logo')
                            ->label('Logo')
                            ->image()
                            ->directory('pharmacy')
                            ->maxSize(2048)
                            ->helperText('Format recommandé : PNG ou JPEG, max 2Mo'),

                        Forms\Components\Textarea::make('invoice_footer')
                            ->label('Pied de facture')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Texte qui apparaîtra en bas de chaque facture'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->label('Ville')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePharmacyInfos::route('/'),
        ];
    }
}
