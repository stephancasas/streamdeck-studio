@props([
    'outerClass' => $attributes->get('outer-class'),
])

<div class="relative {{ $outerClass }}">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1 1">
        <rect width="1" height="1" fill="transparent" />
    </svg>
    <div {{ $attributes->merge(['class' => 'absolute inset-0']) }}>
        {{ $slot }}
    </div>
</div>
