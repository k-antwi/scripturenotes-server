<div x-data="{
    theme: localStorage.getItem('theme') || 'light',
    setTheme(t) {
        this.theme = t;
        localStorage.setItem('theme', t);
        if (t === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
}" class="w-full">
    <div class="flex items-center justify-between px-3 py-2 rounded-xl hover:bg-white/60 transition-all">
        <div class="flex items-center gap-2.5 text-[13px] text-stone-600">
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span>Appearance</span>
        </div>
        <div class="flex items-center gap-1 p-0.5 rounded-lg" style="background: #f0ece3;">
            <button @click="setTheme('light')" :class="{ 'bg-white shadow-sm': theme === 'light' }"
                class="p-1.5 rounded-md transition-all text-stone-600 hover:text-stone-900">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </button>
            <button @click="setTheme('dark')" :class="{ 'bg-white shadow-sm': theme === 'dark' }"
                class="p-1.5 rounded-md transition-all text-stone-600 hover:text-stone-900">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </button>
        </div>
    </div>
</div>
