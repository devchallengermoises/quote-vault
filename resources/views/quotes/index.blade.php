<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Quotes of the Day') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @livewire('show-quotes')
            </div>
            <div class="mt-6">
                @livewire('show-quotes-pagination')
            </div>
        </div>
    </div>
</x-app-layout>
