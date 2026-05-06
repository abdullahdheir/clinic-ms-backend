<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Actions\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Settings extends Page
{
    protected static ?string $navigationLabel = 'الإعدادات';
    protected static ?string $title           = 'إعدادات النظام';

    protected string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'system_name' => Setting::get('system_name', 'كلينيك برو'),
            'support_email' => Setting::get('support_email'),
            'maintenance_mode' => Setting::get('maintenance_mode', false),
            'allow_new_registrations' => Setting::get('allow_new_registrations', true),
            'email_notifications' => Setting::get('email_notifications', true),
            'appointment_reminders' => Setting::get('appointment_reminders', true),
            'reminder_hours_before' => Setting::get('reminder_hours_before', 24),
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('إعدادات عامة')->schema([
                    TextInput::make('system_name')
                        ->label('اسم النظام')
                        ->required(),
                    TextInput::make('support_email')
                        ->label('البريد الإلكتروني للدعم')
                        ->email(),
                    Toggle::make('maintenance_mode')
                        ->label('وضع الصيانة'),
                    Toggle::make('allow_new_registrations')
                        ->label('السماح بتسجيل مستخدمين جدد'),
                ])->columns(2),

                Section::make('إعدادات الإشعارات')->schema([
                    Toggle::make('email_notifications')
                        ->label('الإشعارات عبر البريد الإلكتروني'),
                    Toggle::make('appointment_reminders')
                        ->label('تذكيرات المواعيد'),
                    TextInput::make('reminder_hours_before')
                        ->label('ساعات قبل التذكير')
                        ->numeric()
                        ->default(24),
                ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            Setting::set($key, $value, gettype($value));
        }

        Notification::make()
            ->title('تم الحفظ')
            ->body('تم حفظ الإعدادات بنجاح')
            ->success()
            ->send();
    }

    protected function getActions(): array
    {
        return [
            Action::make('save')
                ->label('حفظ الإعدادات')
                ->action('save')
                ->color('primary'),
        ];
    }
}
