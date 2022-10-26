<div x-data="wireui_color_picker({
    colorNameAsValue: @boolean($colorNameAsValue),

    @if ($attributes->wire('model')->value())
        wireModifiers: @toJs($attributes->wireModifiers()),
        wireModel: @entangle($attributes->wire('model')),
    @endif

    @if ($colors)
        colors: @toJs($getColors())
    @endif
})" {{ $attributes->only(['class', 'wire:key'])->class('relative') }}>
    <x-dynamic-component
        {{ $attributes->except(['class', 'wire:key'])->whereDoesntStartWith('wire:model') }}
        class="text-center pr-10 font-bold cursor-pointer"
        style="color: transparent;"
        :component="WireUi::component('input')"
        x-model="{{ $colorNameAsValue ? 'selected.name' : 'selected.value' }}"
        x-on:click="toggle"
        x-ref="input"
        read-only
        :label="$label"
        :prefix="null"
        :icon="null">
        <x-slot name="prefix">
            <template x-if="selected.value">
                <div
                    class="w-5 h-5 border rounded-full"
                    :style="{ 'background-color': selected.value }"
                ></div>
            </template>
        </x-slot>



        <x-slot name="append">

            {{-- Input Label Override -- will break if used anywhere but icon editor --}}
            <div class="absolute inset-0 pl-4 flex items-center cursor-pointer"
                 x-on:click="toggle">
                <span class="mx-auto text-sm pl-3"
                      x-text="$wire.get('{{ $attributes->get('wire:model') }}Label')"
                      ></span>
            </div>

        </x-slot>
    </x-dynamic-component>

    <x-wireui::parts.popover
        :margin="(bool) $label"
        class="
            max-h-56 py-3 px-2 sm:py-2 sm:px-1 sm:w-72
            overflow-y-auto soft-scrollbar border border-secondary-200
        ">
        <div class="flex flex-wrap items-center justify-center gap-1 sm:gap-0.5 max-w-[18rem] mx-auto">
            <span class="sr-only">dropdown-open</span>

            <template x-for="(color, index) in colors" :key="index">
                <button class="
                        w-6 h-6 rounded shadow-lg border hover:scale-125 transition-all ease-in-out duration-100 cursor-pointer
                        hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-600 sdark:focus:ring-gray-400
                        dark:border-0 dark:hover:ring-2 dark:hover:ring-gray-400
                    "
                    :style="{ 'background-color': color.value }"
                    x-on:click="select(color)"
                    :title="color.name"
                    type="button">
                </button>
            </template>
        </div>
    </x-wireui::parts.popover>
</div>
