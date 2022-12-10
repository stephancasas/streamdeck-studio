@props([
    'label' => $attributes->get('label'),
    'id' => $attributes->get('id'),
    'hexValue' => $attributes->get('hex-value'),
])

<div id="{{ $id }}">
    <div class="flex justify-between mb-1">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">
            {{ $label }}
        </label>
    </div>

    <div class="relative border border-theme">
        <div
             class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-secondary-400">
            <span class="flex items-center self-center pl-1">
                <div class="h-5 w-5 rounded-full border advanced-color-well"
                     x-bind:style="`background-color: ${ {{ $hexValue }} };`"></div>
            </span>
        </div>

        <input
               class="placeholder-secondary-400 dark:bg-secondary-800 dark:text-secondary-400 dark:placeholder-secondary-500 border border-secondary-300 focus:ring-primary-500 focus:border-primary-500 dark:border-secondary-600 form-input no-focus block w-full sm:text-sm transition ease-in-out duration-100 focus:outline-none pl-8 text-center pr-10 font-bold cursor-pointer"
               style="color: transparent;" />

        <div class="absolute inset-0 pl-4 flex items-center cursor-pointer">
            <span class="mx-auto text-sm pl-3 uppercase" x-text="{{ $hexValue }}"></span>
        </div>
    </div>


</div>
