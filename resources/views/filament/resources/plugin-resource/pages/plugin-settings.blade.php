<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold">{{ $record->name }} 设置</h2>
        </div>
        
        <form wire:submit="save">
            {{ $this->form }}
            
            <div class="mt-4 flex justify-end">
                <x-filament::button type="submit">
                    保存设置
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
