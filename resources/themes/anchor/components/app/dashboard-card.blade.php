<a href="{{ $href ?? '' }}" @if($target ?? false) target="_blank" @else wire:navigate @endif class="flex overflow-hidden relative p-5 w-full bg-white rounded-xl border border-gray-200/80 shadow-sm duration-300 ease-out group hover:shadow-md">
    <span class="flex relative flex-col justify-center items-start pr-0 pb-1 space-y-3 h-full">
        <span class="block text-lg font-bold tracking-tight leading-tight text-gray-900">{{ $title ?? '' }}</span>
        <span class="block text-sm text-gray-500">{{ $description ?? '' }}</span>
        <span class="inline-flex relative justify-start items-center -mt-1 mb-2 w-auto text-sm tracking-tight leading-none text-orange-500 font-medium">
            <span class="inline-block flex-shrink-0 mr-0">{{ $linkText ?? '' }}</span>
            <svg class="mt-0.5 ml-2 stroke-1 stroke-orange-500" fill="none" width="10" height="10" viewBox="0 0 10 10" aria-hidden="true"><path class="opacity-0 transition group-hover:opacity-100" d="M0 5h7"></path><path class="transition group-hover:translate-x-[3px]" d="M1 1l4 4-4 4"></path></svg>
        </span>
    </span>
    <img src="{{ $image ?? '' }}" class="w-auto h-32">
</a>
