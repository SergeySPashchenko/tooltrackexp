<?php

namespace App\Filament\Resources\Panel;

use App\Filament\Actions\GeneratePasswordAction;
use App\Filament\Resources\Panel\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = 'Admin';

    public static function getModelLabel(): string
    {
        return __('crud.users.itemTitle');
    }

    public static function getPluralModelLabel(): string
    {
        return __('crud.users.collectionTitle');
    }

    public static function getNavigationLabel(): string
    {
        return __('crud.users.collectionTitle');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                Grid::make(['default' => 1])->schema([
                    FileUpload::make('avatar')
                        ->avatar()
                        ->image()
                        ->alignCenter()
                        ->columnSpanFull()
                        ->directory(fn ($get) => 'avatars/'.Str::slug($get('name')), '-')
                        ->optimize('webp'),
                    TextInput::make('name')
                        ->required()
                        ->string()
                        ->autofocus(),
                    TextInput::make('email')
                        ->required()
                        ->string()
                        ->unique('users', 'email', ignoreRecord: true)
                        ->email(),
                    TextInput::make('password')
                        ->label(__('filament-panels::pages/auth/edit-profile.form.password.label'))
                        ->password()
                        ->required(fn ($livewire) => $livewire instanceof Pages\CreateUser)
                        ->revealable(filament()->arePasswordsRevealable())
                        ->rule(Password::default())
                        ->autocomplete('new-password')
                        ->dehydrated(fn ($state): bool => filled($state))
                        ->dehydrateStateUsing(fn ($state): string => Hash::make($state))
                        ->live(debounce: 500)
                        ->same(function ($livewire) {
                            if ($livewire instanceof Pages\EditUser) {
                                return 'passwordConfirmation';
                            }
                        })
                        ->suffixActions([
                            GeneratePasswordAction::make(),
                        ]),
                    TextInput::make('passwordConfirmation')
                        ->label(__('filament-panels::pages/auth/edit-profile.form.password_confirmation.label'))
                        ->password()
                        ->revealable(filament()->arePasswordsRevealable())
                        ->required()
                        ->visible(function (Get $get, $livewire) {
                            if ($livewire instanceof Pages\EditUser) {
                                return filled($get('password'));
                            }
                        })
                        ->dehydrated(false),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                ImageColumn::make('avatar')
                    ->circular(),

                TextColumn::make('name'),

                TextColumn::make('email'),

                TextColumn::make('deleted_at')->since(),
            ])
            ->filters([Tables\Filters\TrashedFilter::make()])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }
}
