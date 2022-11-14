<x-app-layout>
    <header class="flex items-center border-b border-theme h-[50px]">
        <x-identity />
        
        {{-- font preload workaround --}}
        <div class="h-0 opacity-0 overflow-hidden">
            <span style="font-family: 'Geo';">{{ config('app.name') }}</span>
            <span style="font-family: 'VT323';">{{ config('app.name') }}</span>
        </div>

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
