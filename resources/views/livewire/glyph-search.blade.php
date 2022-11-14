<div @class(['h-full flex-grow w-0', 'flex flex-col']) x-data="{ query: '', placeholder: '', placeholders: $wire.entangle('placeholders').defer, }"
     x-init="placeholder = placeholders[0];
     setInterval(() => { placeholder = placeholders[placeholders.findIndex((str) => str === placeholder) + 1] ?? placeholders[0]; }, 3000);">

    {{-- Search Input --}}
    <div class='relative flex items-center border-b border-theme h-[50px]'>
        <div @class([
            'flex items-center h-[50px] w-[50px] p-3',
            'text-neutral-300 dark:text-neutral-500',
            'mx-auto',
        ])>
            @fontawesome('search', 'light')
        </div>
        <input type="text"
               x-bind:placeholder='`Search for "${placeholder}" or something else...`'
               @class(['no-focus bg-transparent', 'w-full pr-2 py-1'])
               @input.debounce.500="(!query || ['on', 'no'].includes(query) || query.length > 2) && $wire.call('search', query)"
               x-model="query" />

        <div class="absolute right-0 inset-y-0 w-[50px] flex items-center px-2"
             x-show="query" x-transition.scale.60 @click="query = ''; $wire.call('search', query)">
            <div class="inset-button-theme w-4 mx-auto">
                @fontawesome('circle-xmark')
            </div>
        </div>
    </div>

    {{-- Results --}}
    <div class="relative h-0 flex-grow overflow-scroll">

        {{-- No Input --}}
        {{-- <div class="flex items-center h-full w-full" x-show="!query">
            <div class="text-neutral-200 dark:text-neutral-600 mx-auto">
                <div class="mx-auto h-48 w-48">@fontawesome('cat-space')</div>
                <div class="text-lg font-semibold text-neutral-300 dark:text-neutral-500">
                    Let's give that deck a fresh new face.
                </div>
            </div>
        </div> --}}

        {{-- Shimmer Items --}}
        <div class="absolute inset-0 pt-8 px-4 grid grid-cols-4 gap-y-8 overflow-hidden hidden"
             wire:loading.class.remove="hidden" wire:target="search" x-show="query">
            @for ($i = 0; $i < 80; $i++)
                <div class="flex col-span-1">
                    <div @class([
                        'flex flex-col mx-auto h-40 w-40 pt-4',
                        'border border-theme-light',
                    ])>
                        <div class="h-20 w-20 mx-auto text-shimmer">
                            @fontawesome('cube')
                        </div>
                        <div class="flex py-4 px-2 text-center">
                            <div class="mx-auto bg-shimmer">
                                <span class="opacity-0">placeholder</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endfor
        </div>

        {{-- Result Items --}}
        <div class="py-8 px-4 grid grid-cols-4 gap-y-8" wire:loading.class="hidden"
             wire:target="search">
            @foreach ($this->results as $glyphId => $glyphSvg)
                <div class="flex col-span-1" wire:key="{{ $glyphId }}">
                    <div @class([
                        'flex flex-col mx-auto h-40 w-40',
                        'border border-theme-light',
                        'hover-theme-neutral transition-all',
                        'cursor-pointer',
                    ]) wire:click="chooseGlyph('{{ $glyphId }}')">
                        {{-- double event listener because svgs are goofy --}}
                        <div class="relative pt-4 h-24 w-24 mx-auto" wire:click="chooseGlyph('{{ $glyphId }}')">
                            {{-- Actual Glyph --}}
                            {!! $glyphSvg !!}

                            {{-- Icon Protection / Click Intercept --}}
                            <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII="
                                 class="absolute inset-0 h-full w-full">
                        </div>
                        <div class="flex w-full py-4 px-2 text-center">
                            <span class="mx-auto font-semibold text-sm truncate">{{ Str::headline($glyphId) }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>

</div>
