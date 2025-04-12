<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}
        
        <div class="mt-4 flex justify-end">
            <x-filament::button type="submit">
                保存设置
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
