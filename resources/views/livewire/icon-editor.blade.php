<div class="app-icon-editor flex flex-col"
     x-data="IconEditor">

    <div class='relative flex items-center border-b border-theme h-[50px]'>
        <input type="text"
               placeholder="My Icon"
               @class(['no-focus bg-transparent', 'w-full px-2 py-1', 'text-center'])
               x-model="label" />

        {{-- Download Button --}}
        <div @class([
            'absolute left-0 inset-y-0 w-[50px]',
            'flex items-center ',
            'border-r border-theme',
            'inset-button-theme',
        ]) @click="download()">
            <div @class(['flex items-center', 'w-5 mx-auto'])>
                @fontawesome('download', 'light')
            </div>
        </div>

        {{-- Save Button --}}
        <div @class([
            'absolute right-0 inset-y-0 w-[50px]',
            'flex items-center ',
            'border-l border-theme',
            'inset-button-theme',
        ]) wire:click="collectIcon">
            <div @class(['flex items-center', 'w-6 mx-auto'])>
                @fontawesome('rectangle-history-circle-plus', 'light')
            </div>
        </div>
    </div>

    {{-- Icon Preview --}}
    <div class="m-16">

        {{-- visibility restored in icon-editor.js -- see note --}}
        <div id="icon-canvas-aggregate" class="relative transition-all opacity-0 translate-y-2">

            {{-- Canvas for Display --}}
            <div class="absolute inset-0 z-30" wire:ignore>
                <x-inset-square>
                    <img x-ref="pngPreview" id="png-preview" class="absolute inset-0 opacity-0"
                         @dragend="$wire.call('telemetry', 'icon-export-drag')">
                </x-inset-square>
            </div>

            {{-- Canvas for Edit --}}
            <x-inset-square outer-class="z-20">
                <div id="icon-canvas"
                     class="flex flex-col h-full w-full pt-4 rounded-[45px] shadow-md bg-{{ $this->canvasColor }}"
                     x-bind:class="labelVisibility ? 'pt-4' : 'pt-6'">
                    {{-- Glyph --}}
                    <div class="h-0 flex-grow p-5 text-{{ $this->glyphColor }}">
                        <x-inset-square outer-class="h-full w-full">
                            @isset($this->glyph)
                                {!! $this->glyph->preview_svg !!}
                            @endisset
                        </x-inset-square>
                    </div>
                    {{-- Label --}}
                    <div @class([
                        'font-icon text-6xl text-center uppercase w-full truncate text-clip overflow-hidden px-2',
                        'relative',
                    ]) x-show="labelVisibility"
                         x-bind:style="`font-family: '${labelTypeface || 'VT323'}';`">
                        <span x-text="label" class="text-{{ $this->labelColor }}"></span>
                        <span x-show="!label">&nbsp;</span>
                    </div>
                    {{-- Spacer --}}
                    <div class="h-8 font-icon text-6xl text-center uppercase opacity-0">&nbsp;</div>
                </div>
            </x-inset-square>

            {{-- Canvas for Render --}}
            <div class="absolute inset-0 z-10">
                <x-inset-square>
                    <div id="icon-canvas-render" x-ref="iconCanvasForRender"
                         class="flex flex-col h-full w-full rounded-[45px] bg-{{ $this->canvasColor }}"
                         x-bind:class="labelVisibility ? 'pt-4' : 'pt-6'">
                        {{-- Glyph --}}
                        <div class="h-0 flex-grow p-5 text-{{ $this->glyphColor }}">
                            <x-inset-square outer-class="h-full w-full">
                                @isset($this->glyph)
                                    {!! $this->glyph->preview_svg !!}
                                @endisset
                            </x-inset-square>
                        </div>
                        {{-- Label --}}
                        {{-- label is translated upward by 2rem -- this fixes a flex offset issue in html2canvas.js --}}
                        <div @class([
                            'font-icon text-6xl text-center uppercase w-full truncate text-clip px-2',
                            'relative',
                            '-translate-y-8',
                        ]) x-show="labelVisibility"
                             x-bind:style="`font-family: '${labelTypeface || 'VT323'}';`">
                            <span x-text="label" class="text-{{ $this->labelColor }}"></span>
                            <span x-show="!label">&nbsp</span>
                        </div>
                        {{-- Spacer --}}
                        <div class="h-8 font-icon text-6xl text-center uppercase">&nbsp;</div>
                    </div>
                </x-inset-square>
            </div>
        </div>
    </div>

    {{-- Options --}}
    <div class="px-4 pt-2 pb-3 border-t border-b border-theme">

        {{-- Label Display --}}
        <div class="grid grid-cols-2 gap-x-2">
            {{-- Label Text --}}
            <div class="col-span-1">
                <div class="flex items-end">
                    <div class="w-0 flex-grow">
                        <x-input label="Label" placeholder="My Icon" x-model="label" />
                    </div>
                    <div class="relative flex items-center h-full px-2 ">
                        {{-- mock border --}}
                        <div class="absolute inset-0 border-t border-r border-b border-theme"></div>
                        {{-- placeholder to ensure checkbox alignment with text input --}}
                        <div class="w-0 opacity-0 overflow-hidden">
                            <x-input />
                        </div>
                        {{-- actual checkbox --}}
                        <x-checkbox lg x-model="labelVisibility" />
                    </div>
                </div>
            </div>

            {{-- Label Typeface --}}
            <x-select
                      class="w-full"
                      label="Typeface"
                      placeholder="None"
                      :clearable="false"
                      :options="['VT323', 'Geo']"
                      wire:model="labelTypeface" />
        </div>

        {{-- Colors --}}
        <div class="grid grid-cols-3 gap-x-2 pt-2">
            <div class="col-span-1">
                <x-color-picker color-name-as-value label="Glyph" wire:model="glyphColor" />
            </div>
            <div class="col-span-1">
                <x-color-picker color-name-as-value label="Canvas" wire:model="canvasColor" />
            </div>
            <div class="col-span-1">
                <x-color-picker color-name-as-value label="Label" wire:model="labelColor" />
            </div>
        </div>

        {{-- Automatic Schemes --}}
        <div class="flex pt-2">
            <x-select
                      class="w-full"
                      label="Color Scheme"
                      placeholder="None"
                      :clearable="false"
                      :options="$this->getSchemeOptions()"
                      wire:model.defer="colorScheme" />
        </div>

    </div>
</div>
