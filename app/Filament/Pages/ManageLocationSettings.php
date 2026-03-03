<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Schemas\Schema;
// use Filament\Forms\Components\Section;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use App\Models\Setting;

class ManageLocationSettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    // Hapus 'static' di sini
    protected string $view = 'filament.pages.manage-location-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $setting = Setting::first();
        if ($setting) {
            $this->form->fill($setting->toArray());
        } else {
            $this->form->fill();
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Location Settings')
                    ->description('Set the office location and the acceptable radius for mobile attendance.')
                    ->components([
                        TextInput::make('latitude')
                            ->required()
                            ->numeric()
                            ->label('Latitude'),
                        TextInput::make('longitude')
                            ->required()
                            ->numeric()
                            ->label('Longitude'),
                        TextInput::make('radius')
                            ->required()
                            ->numeric()
                            ->label('Radius (in meters)')
                            ->helperText('Maximum distance from the office location allowed for check-in/out.'),
                    ])
                    ->columns(3),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $setting = Setting::first();
        if ($setting) {
            $setting->update($data);
        } else {
            Setting::create($data);
        }

        Notification::make()
            ->title('Settings Saved')
            ->success()
            ->send();
    }
}