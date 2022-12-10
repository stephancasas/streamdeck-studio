<div class="h-0 flex-grow overflow-scroll" x-data="IconCollection">
    {{-- Controls --}}
    <div class='relative flex items-center border-b border-theme h-[50px]'>
        {{-- Collection Name --}}
        <input type="text"
               x-bind:placeholder="`My ${placeholder} StreamDeck Theme`"
               @class(['no-focus bg-transparent', 'w-full px-2 py-1'])
               x-model="collectionName" />

        {{-- Download Button --}}
        <div @class([
            'absolute right-0 inset-y-0 w-[50px]',
            'flex items-center ',
            'border-l border-theme',
            'inset-button-theme',
        ]) @click="downloadCollection()" {{ Popper::pop('Download Collection') }}>
            <div @class(['flex items-center', 'w-5 mx-auto'])>
                @fontawesome('download', 'light')
            </div>
        </div>
    </div>

    {{-- Grid --}}
    <div class="grid grid-cols-4 gap-6 p-6"
         x-ref="collectionGrid"
         @collect-editor-icon.window="collectEditorIcon($event.detail)"
         wire:ignore>
        <template id="grid-render-template">
            <div x-data="{ hover: false }"
                 class="relative col-span-1 cursor-pointer rounded-xl shadow-md transition-all"
                 style="transform: scale(1);"
                 @mouseenter="hover=true" @mouseleave="hover=false">
                <div
                     @class([
                         'absolute top-0 right-0 flex items-center',
                         'h-6 w-6 p-0.5 z-20',
                         'shadow-md rounded-full',
                         'bg-slate-100 dark:bg-neutral-700',
                         'hover:bg-slate-200 hover:dark:bg-neutral-800',
                         'text-negative-500 border border-theme-light',
                     ])
                     style="margin-right: -0.5rem; margin-top: -0.5rem;"
                     x-show="hover" x-transition.scale.60 @click="deleteIcon($root)">
                    @fontawesome('xmark', 'light')
                </div>
                <x-inset-square class="grid-preview-target z-10"
                                @click="loadIconInEditor($root.getAttribute('data--icon-id'))" />
            </div>
        </template>
    </div>
</div>
