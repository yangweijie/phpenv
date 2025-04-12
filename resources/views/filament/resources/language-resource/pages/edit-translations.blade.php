<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold">编辑 {{ $record->name }} 翻译</h2>
            
            <div class="flex space-x-4">
                <select
                    class="block w-full rounded-lg border border-gray-300 bg-white py-2 px-3 text-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:focus:border-primary-500"
                    wire:model.live="selectedFile"
                    wire:change="changeFile($event.target.value)"
                >
                    @foreach($this->getAvailableFiles() as $file => $label)
                        <option value="{{ $file }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <form wire:submit="save">
            {{ $this->form }}
            
            <div class="mt-4 flex justify-end">
                <x-filament::button type="submit">
                    保存翻译
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
