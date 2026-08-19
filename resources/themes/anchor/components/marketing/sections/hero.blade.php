<section class="relative flex flex-col justify-center items-center -mt-24 w-full min-h-screen bg-white overflow-hidden">
    {{-- Background gradient --}}
    <div class="absolute inset-0 bg-gradient-to-br from-blue-50 via-white to-indigo-50 pointer-events-none"></div>
    <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-blue-100/40 rounded-full -translate-y-1/2 translate-x-1/4 blur-3xl pointer-events-none"></div>

    <div class="relative flex flex-col flex-1 gap-10 justify-between items-center px-8 pt-32 mx-auto w-full max-w-2xl text-left md:px-12 xl:px-20 lg:pt-32 lg:pb-16 lg:max-w-7xl lg:flex-row">
        <div class="w-full lg:w-1/2">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 mb-6 text-xs font-medium text-blue-700 bg-blue-100 rounded-full">
                <x-phosphor-shield-check class="w-3.5 h-3.5" />
                UK Pension Tracing Service
            </div>
            <h1 class="text-5xl font-bold tracking-tight text-zinc-900 sm:text-6xl md:text-[64px] text-balance leading-[1.1]">
                Find &amp; Manage<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">Your Lost Pensions</span>
            </h1>
            <p class="mt-6 text-lg text-zinc-500 leading-relaxed max-w-md">
                Billions of pounds sit in forgotten pension pots. PensionTrack helps you locate lost pensions from past employers and keep all your retirement savings in one place.
            </p>
            <div class="flex flex-col gap-3 items-start mt-8 sm:flex-row">
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 text-base font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm shadow-blue-200">
                    <x-phosphor-magnifying-glass class="w-5 h-5" />
                    Find My Pensions
                </a>
                <a href="{{ route('register') }}?fa=1" class="inline-flex items-center gap-2 px-6 py-3 text-base font-semibold text-zinc-700 bg-white border border-zinc-200 rounded-xl hover:bg-zinc-50 transition-colors">
                    <x-phosphor-briefcase class="w-5 h-5" />
                    I'm a Financial Advisor
                </a>
            </div>
            <div class="flex items-center gap-6 mt-10">
                <div class="text-center">
                    <p class="text-2xl font-bold text-zinc-900">&pound;26bn+</p>
                    <p class="text-xs text-zinc-500 mt-0.5">Unclaimed pensions in the UK</p>
                </div>
                <div class="w-px h-10 bg-zinc-200"></div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-zinc-900">1 in 3</p>
                    <p class="text-xs text-zinc-500 mt-0.5">People have lost a pension</p>
                </div>
                <div class="w-px h-10 bg-zinc-200"></div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-zinc-900">Free</p>
                    <p class="text-xs text-zinc-500 mt-0.5">To trace &amp; manage</p>
                </div>
            </div>
        </div>

        {{-- Dashboard preview card --}}
        <div class="w-full lg:w-1/2 flex justify-center lg:justify-end">
            <div class="relative w-full max-w-md">
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                        <p class="text-xs text-blue-200 font-medium">Total estimated value</p>
                        <p class="text-3xl font-bold text-white mt-0.5">&pound;87,450</p>
                        <p class="text-xs text-blue-200 mt-1">Across 4 pension pots</p>
                    </div>
                    <div class="p-4 space-y-3">
                        @foreach([['Aviva Workplace Pension', 'Active', 'Tesco PLC', '42,200', 'green'], ['Legal &amp; General', 'Dormant', 'HSBC Bank', '28,100', 'yellow'], ['Nest Pension', 'Active', 'Amazon UK', '17,150', 'green']] as [$name, $status, $employer, $value, $color])
                        <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 border border-zinc-100">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                    <x-phosphor-buildings class="w-4 h-4 text-blue-600" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-zinc-900">{{ $name }}</p>
                                    <p class="text-xs text-zinc-500">{{ $employer }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-zinc-900">&pound;{{ $value }}</p>
                                <span class="text-xs px-1.5 py-0.5 rounded-full {{ $color === 'green' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">{{ $status }}</span>
                            </div>
                        </div>
                        @endforeach
                        <a href="{{ route('register') }}" class="flex items-center justify-center gap-2 w-full py-2.5 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <x-phosphor-plus class="w-4 h-4" />
                            Add another pension
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Key facts bar --}}
    <div class="relative flex-shrink-0 flex border-t border-zinc-200 items-center w-full bg-white">
        <div class="grid grid-cols-1 px-8 py-10 mx-auto space-y-5 max-w-7xl h-auto divide-y lg:space-y-0 lg:divide-y-0 divide-zinc-200 lg:py-8 lg:divide-x md:px-12 lg:px-20 lg:divide-zinc-200 lg:grid-cols-3">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <x-phosphor-magnifying-glass class="w-5 h-5 text-blue-600" />
                </div>
                <div>
                    <h3 class="font-semibold text-zinc-900">Find Lost Pensions</h3>
                    <p class="mt-1 text-sm text-zinc-500">Search the UK pension tracing database by employer name and dates of employment.</p>
                </div>
            </div>
            <div class="pt-5 lg:pt-0 lg:px-10 flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                    <x-phosphor-layout class="w-5 h-5 text-indigo-600" />
                </div>
                <div>
                    <h3 class="font-semibold text-zinc-900">Manage Everything</h3>
                    <p class="mt-1 text-sm text-zinc-500">Keep all your workplace, personal and SIPP pensions in a single organised dashboard.</p>
                </div>
            </div>
            <div class="pt-5 lg:pt-0 lg:px-10 flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                    <x-phosphor-trend-up class="w-5 h-5 text-green-600" />
                </div>
                <div>
                    <h3 class="font-semibold text-zinc-900">Track Your Wealth</h3>
                    <p class="mt-1 text-sm text-zinc-500">Monitor estimated values across all your pensions and plan for retirement with confidence.</p>
                </div>
            </div>
        </div>
    </div>
</section>
