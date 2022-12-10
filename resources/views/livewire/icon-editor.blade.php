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
        ]) @click="download()" {{ Popper::pop('Download PNG') }}>
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
        ]) wire:click="collectIcon" {{ Popper::pop('Add to Collection') }}>
            <div @class(['flex items-center', 'w-6 mx-auto'])>
                @fontawesome('rectangle-history-circle-plus', 'light')
            </div>
        </div>
    </div>

    {{-- Icon Preview --}}
    <div class="m-16 z-10">

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
                     x-bind:class="labelVisibility ? 'pt-4' : 'pt-6'"
                     x-bind:style="useAdvancedColorUi ? `background-color: ${advancedColor.canvas} !important;` : ''">
                    {{-- Glyph --}}
                    <div class="h-0 flex-grow p-5 text-{{ $this->glyphColor }}"
                         x-bind:style="useAdvancedColorUi ? `color: ${advancedColor.glyph} !important;` : ''">
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
                        <span x-text="label" class="text-{{ $this->labelColor }}"
                              x-bind:style="useAdvancedColorUi ? `color: ${advancedColor.label} !important;` : ''"></span>
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
                         x-bind:class="labelVisibility ? 'pt-4' : 'pt-6'"
                         x-bind:style="useAdvancedColorUi ? `background-color: ${advancedColor.canvas} !important;` : ''">
                        {{-- Glyph --}}
                        <div class="h-0 flex-grow p-5 text-{{ $this->glyphColor }}"
                             x-bind:style="useAdvancedColorUi ? `color: ${advancedColor.glyph} !important;` : ''">
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
                            <span x-text="label" class="text-{{ $this->labelColor }}"
                                  x-bind:style="useAdvancedColorUi ? `color: ${advancedColor.label} !important;` : ''"></span>
                            <span x-show="!label">&nbsp</span>
                        </div>
                        {{-- Spacer --}}
                        <div class="h-8 font-icon text-6xl text-center uppercase">&nbsp;</div>
                    </div>
                </x-inset-square>
            </div>

            {{-- Mask for Alpha-enabled Colors --}}
            <div class="absolute inset-0 z-10">
                <x-inset-square outer-class="bg-gray-50 dark:bg-neutral-800 rounded-[45px]" />

                {{-- uncomment to include alpha patter -- i don't like it --}}
                {{-- <x-inset-square outer-class="bg-gray-50 dark:bg-neutral-800 rounded-[45px] alpha-checkers" /> --}}
            </div>

        </div>
    </div>

    {{-- Options --}}
    <div class="px-4 pt-2 pb-3 border-t border-b border-theme z-20">

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

        {{-- Tailwind Colors --}}
        <div class="grid grid-cols-3 gap-x-2 pt-2" x-show="!useAdvancedColorUi">
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

        {{-- Advanced Colors --}}
        <div class="grid grid-cols-3 gap-x-2 pt-2" x-show="useAdvancedColorUi">
            <div class="col-span-1">
                <x-ui-color-picker label="Glyph" id="advanced-color-glyph" hex-value="advancedColor.glyph" />
            </div>
            <div class="col-span-1">
                <x-ui-color-picker label="Canvas" id="advanced-color-canvas" hex-value="advancedColor.canvas" />
            </div>
            <div class="col-span-1">
                <x-ui-color-picker label="Label" id="advanced-color-label" hex-value="advancedColor.label" />
            </div>
        </div>

        {{-- Color UI Control --}}
        <div class="grid grid-cols-7 gap-x-2 pt-2">

            {{-- Scheme --}}
            <x-select
                      class="col-span-5"
                      label="Color Scheme"
                      placeholder="None"
                      :clearable="false"
                      :options="$this->getSchemeOptions()"
                      wire:model.defer="colorScheme"
                      disabled="{{ $this->useAdvancedColorUi }}" />

            {{-- Advanced Color --}}
            <div class="relative col-span-2">
                <div class="absolute bottom-0">
                    <div class="relative">
                        <div class="opacity-0 z-10">
                            <x-input />
                        </div>
                        <div class="absolute bottom-0 inset-x-0 h-full flex items-center z-20">
                            <div class="mx-auto">
                                <x-toggle lg label="Advanced" wire:model="useAdvancedColorUi" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
