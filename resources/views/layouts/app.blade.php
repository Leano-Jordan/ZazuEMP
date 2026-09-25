<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Zazu' }} · Zazu EMP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#dcecf2] text-slate-900 antialiased">
    <div class="min-h-screen lg:flex">
        <aside class="hidden w-64 shrink-0 border-r border-[#b9d3dc] bg-[#d3e8ef] lg:flex lg:flex-col">
            <div class="flex h-20 items-center border-b border-[#b9d3dc] bg-[#d9edf3] px-6">
                <a href="{{ route('work.index') }}" class="text-2xl font-semibold tracking-tight text-slate-950">zazu<span class="text-sky-600">.</span></a>
            </div>

            <nav class="flex-1 space-y-1 p-4">
                @php
                    $items = [
                        ['label' => 'Work', 'route' => 'work.index'],
                        ['label' => 'Customers', 'route' => 'customers.index'],
                        ['label' => 'Capabilities', 'route' => 'capabilities.index'],
                    ];
                @endphp

                @foreach ($items as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        class="flex items-center rounded-xl px-3 py-2.5 text-sm font-medium {{ request()->routeIs($item['route']) ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }}"
                    >
                        {{ $item['label'] }}
                    </a>
                @endforeach

                <div class="pt-5">
                    <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Coming next</p>
                    <div class="space-y-1 text-sm text-slate-500">
                        <div class="rounded-lg px-3 py-2">Quotes</div>
                        <div class="rounded-lg px-3 py-2">Calendar</div>
                        <div class="rounded-lg px-3 py-2">Suppliers</div>
                        <div class="rounded-lg px-3 py-2">Inventory</div>
                        <div class="rounded-lg px-3 py-2">Reports</div>
                    </div>
                </div>
            </nav>

            <div class="border-t border-[#b9d3dc] p-4">
                <div class="rounded-xl border border-[#bdd6df] bg-[#c9e1e9] px-3 py-3">
                    <p class="text-xs font-semibold text-slate-600">Zazu EMP</p>
                    <p class="mt-1 text-xs text-slate-500">Operational workspace</p>
                </div>
            </div>
        </aside>

        <main class="min-w-0 flex-1">
            <header class="border-b border-[#bdd6df] bg-[#e1f0f5]">
                <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 sm:px-8">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-[0.14em] text-slate-400">Zazu EMP</p>
                        <h1 class="mt-1 text-xl font-semibold tracking-tight text-slate-950">{{ $heading ?? $title ?? 'Workspace' }}</h1>
                    </div>
                    @isset($headerAction)
                        {{ $headerAction }}
                    @endisset
                </div>
            </header>

            <div class="mx-auto max-w-7xl px-5 py-6 sm:px-8">
                @if (session('success'))
                    <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 shadow-sm">
                        <p class="font-semibold">Please check the highlighted information.</p>
                    </div>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
