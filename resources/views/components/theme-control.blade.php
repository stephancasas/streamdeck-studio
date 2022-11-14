<div class="flex items-center relative h-full w-[50px] border-l border-theme overflow-hidden"
     x-data="Theme"
     @click="toggle()">
    <div class="app-theme-set inset-button-theme"
         x-bind:class="theme === 'dark' && 'off'">
        @fontawesome('sun', 'light')
    </div>
    <div class="app-theme-set inset-button-theme"
         x-bind:class="theme !== 'dark' && 'off'">
        @fontawesome('moon', 'light')
    </div>
</div>
