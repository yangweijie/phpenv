<div class="relative">
    <button
        type="button"
        class="flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none"
        x-data
        x-on:click="$refs.languageMenu.classList.toggle('hidden')"
    >
        <span class="sr-only">切换语言</span>
        <span class="text-sm font-medium uppercase">{{ $currentLocale }}</span>
    </button>
    
    <div
        x-ref="languageMenu"
        class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-50 hidden"
    >
        @foreach($languages as $language)
            <button
                type="button"
                class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ $currentLocale === $language->code ? 'bg-gray-100 dark:bg-gray-700' : '' }}"
                wire:click="switchLanguage('{{ $language->code }}')"
            >
                {{ $language->name }}
            </button>
        @endforeach
    </div>
</div>
