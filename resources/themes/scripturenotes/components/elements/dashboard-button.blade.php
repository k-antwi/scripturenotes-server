<button {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-stone-700 bg-white border border-stone-200 rounded-xl hover:bg-stone-50 hover:border-stone-300 shadow-sm transition-all']) }}>
    {{ $slot }}
</button>
