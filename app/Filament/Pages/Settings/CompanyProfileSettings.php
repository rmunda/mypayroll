<?php

namespace App\Filament\Pages\Settings;

use App\Models\CompanySetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompanyProfileSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Company Profile';

    protected static string|BackedEnum|null $navigationIcon = 'lucide-building';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.settings.company-profile-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill(
            CompanySetting::first()?->toArray() ?? []
        );
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'lg' => 2,
                ])
                    ->schema([

                        Section::make('Basic Information')
                            ->columns(1)
                            ->schema([
                                TextInput::make('company_name')
                                    ->required(),

                                TextInput::make('tagline'),

                                FileUpload::make('logo')
                                    ->image(),
                            ]),

                        Section::make('Address')
                            ->columns(2)
                            ->schema([
                                TextInput::make('address_line1')
                                    ->columnSpanFull(),

                                TextInput::make('address_line2')
                                    ->columnSpanFull(),

                                TextInput::make('city'),
                                TextInput::make('state'),
                                TextInput::make('pincode'),
                                TextInput::make('country'),
                            ]),

                        Section::make('Contact')
                            ->columns(1)
                            ->schema([
                                TextInput::make('phone'),
                                TextInput::make('email'),
                                TextInput::make('website'),
                            ]),

                        Section::make('Statutory Details')
                            ->columns(1)
                            ->schema([
                                TextInput::make('gstin'),
                                TextInput::make('pan'),
                                TextInput::make('cin'),
                                TextInput::make('epf_registration_no'),
                                TextInput::make('esic_registration_no'),
                                TextInput::make('pt_registration_no'),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Changes')
                ->icon('heroicon-o-check')
                ->action(fn () => $this->save()),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        CompanySetting::updateOrCreate(
            ['id' => 1],
            $data
        );

        Notification::make()
            ->title('Company profile saved successfully')
            ->success()
            ->send();
    }
}