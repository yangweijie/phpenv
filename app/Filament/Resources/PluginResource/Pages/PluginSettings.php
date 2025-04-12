<?php

namespace App\Filament\Resources\PluginResource\Pages;

use App\Filament\Resources\PluginResource;
use App\Models\Plugin;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;

class PluginSettings extends Page
{
    protected static string $resource = PluginResource::class;

    protected static string $view = 'filament.resources.plugin-resource.pages.plugin-settings';

    public ?array $data = [];

    public $record;

    public function mount($record): void
    {
        $this->record = Plugin::findOrFail($record);

        if (!$this->record->status) {
            $this->redirect(route('filament.admin.resources.plugins.index'));
            $this->notify('danger', '插件未激活，无法设置');
            return;
        }

        $this->form->fill($this->record->settings ?? []);
    }

    public function form(Form $form): Form
    {
        $instance = $this->record->getMainClassInstance();

        if (!$instance || !method_exists($instance, 'getSettingsForm')) {
            return $form->schema([]);
        }

        $schema = $instance->getSettingsForm();

        if (!is_array($schema)) {
            $schema = [];
        }

        return $form->schema($schema);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        try {
            $this->record->updateSettings($data);

            Notification::make()
                ->title('设置已保存')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('保存设置失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
