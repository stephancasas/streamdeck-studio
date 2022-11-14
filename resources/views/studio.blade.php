<x-app-layout>
    <header class="flex items-center border-b border-theme h-[50px]">
        <x-identity />

        {{-- font preload workaround --}}
        <div class="h-0 opacity-0 overflow-hidden">
            <span style="font-family: 'Geo';">{{ config('app.name') }}</span>
            <span style="font-family: 'VT323';">{{ config('app.name') }}</span>
        </div>

        {{-- GitHub Button --}}
        <a @class([
            'relative h-full w-[50px] ml-auto',
            'flex items-center',
            'border-l border-theme',
            'inset-button-theme',
        ]) href="https://github.com/stephancasas/streamdeck-studio" target="_blank">
            <div @class(['flex items-center', 'w-5 mx-auto'])>
                @fontawesome('github', 'brands')
            </div>
        </a>

        
        <x-theme-control />
    </header>

    <main class="flex flex-grow h-0">
        <livewire:glyph-search />

        <aside class="app-workbench">
            <livewire:icon-editor />
            <livewire:icon-collection />

            <x-acknowledgements />
        </aside>
    </main>



    <script>
        document.addEventListener('DOMContentLoaded', () => document.querySelector('input').focus());
    </script>
</x-app-layout>
