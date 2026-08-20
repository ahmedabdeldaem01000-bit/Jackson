<!DOCTYPE html>
<html
    lang="ar"
    dir="rtl"
>
<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        {{ $title ?? 'حسابي' }}
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    @livewireStyles

</head>

<body class="min-h-screen bg-[#faf8f6] text-[#4b2a22] antialiased">

    <div
        x-data="{ sidebarOpen: false }"
        class="min-h-screen"
    >

        {{-- Mobile Overlay --}}
        <div
            x-show="sidebarOpen"
            x-transition.opacity
            class="fixed inset-0 z-40 bg-black/40 lg:hidden"
            @click="sidebarOpen = false"
        ></div>


        {{-- ========================================================= --}}
        {{-- Sidebar --}}
        {{-- ========================================================= --}}

        <aside
            class="fixed inset-y-0 right-0 z-50 flex w-[280px] flex-col border-l border-[#eaded8] bg-white transition-transform duration-300 lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full'"
        >

            {{-- Brand --}}
            <div class="flex h-20 items-center border-b border-gray-100 px-6">

                <a
                    href="{{ route('customer.profile') }}"
                    class="flex items-center gap-3"
                >

                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#5b3025] text-white shadow-sm"
                    >
                        ✂
                    </div>

                    <div>

                        <p class="text-base font-bold text-[#4b2a22]">
                            Barber
                        </p>

                        <p class="text-xs text-gray-400">
                            Customer Area
                        </p>

                    </div>

                </a>

            </div>


            {{-- Customer --}}
            <div class="border-b border-gray-100 p-5">

                <div class="flex items-center gap-3">

                    @php
                        $sidebarCustomer = auth('customer')->user();
                    @endphp

                    @if($sidebarCustomer?->avatar)

                        <img
                            src="{{ asset($sidebarCustomer->avatar) }}"
                            alt="{{ $sidebarCustomer->name }}"
                            class="h-12 w-12 rounded-2xl object-cover"
                        >

                    @else

                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#f3e5de] font-bold text-[#7d4a3a]"
                        >
                            {{ strtoupper(mb_substr($sidebarCustomer?->name ?? 'U', 0, 1)) }}
                        </div>

                    @endif

                    <div class="min-w-0">

                        <p class="truncate text-sm font-bold text-[#4b2a22]">
                            {{ $sidebarCustomer?->name }}
                        </p>

                        <p class="truncate text-xs text-gray-400">
                            {{ $sidebarCustomer?->email }}
                        </p>

                    </div>

                </div>

            </div>


            {{-- Navigation --}}
            <nav class="flex-1 space-y-2 overflow-y-auto p-4">

                {{-- Profile --}}
                <a
                    href="{{ route('customer.profile') }}"
                    class="group flex items-center gap-3 rounded-2xl px-4 py-3.5 text-sm font-bold transition
                    {{ request()->routeIs('customer.profile')
                        ? 'bg-[#f8eee9] text-[#6d4033]'
                        : 'text-gray-600 hover:bg-[#fcf8f6] hover:text-[#6d4033]' }}"
                >

                    <span
                        class="flex h-10 w-10 items-center justify-center rounded-xl
                        {{ request()->routeIs('customer.profile')
                            ? 'bg-white text-[#9a6252]'
                            : 'bg-[#faf7f5] text-gray-400' }}"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <circle cx="12" cy="8" r="3.5"/>
                            <path d="M5 21a7 7 0 0 1 14 0"/>
                        </svg>
                    </span>

                    <span>
                        الملف الشخصي
                    </span>

                </a>


                {{-- All Bookings --}}
                <a
                    href="{{ route('customer.profile.bookings') }}"
                    class="group flex items-center gap-3 rounded-2xl px-4 py-3.5 text-sm font-bold transition
                    {{ request()->routeIs('customer.profile.bookings')
                        ? 'bg-[#f8eee9] text-[#6d4033]'
                        : 'text-gray-600 hover:bg-[#fcf8f6] hover:text-[#6d4033]' }}"
                >

                    <span
                        class="flex h-10 w-10 items-center justify-center rounded-xl
                        {{ request()->routeIs('customer.profile.bookings')
                            ? 'bg-white text-[#9a6252]'
                            : 'bg-[#faf7f5] text-gray-400' }}"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <rect
                                x="3"
                                y="5"
                                width="18"
                                height="16"
                                rx="2"
                            />
                            <path d="M16 3v4M8 3v4M3 10h18"/>
                        </svg>
                    </span>

                    الحجوزات السابقة

                </a>


                {{-- Current Booking --}}
                <a
                    href="{{ route('customer.profile.current-booking') }}"
                    class="group flex items-center gap-3 rounded-2xl px-4 py-3.5 text-sm font-bold transition
                    {{ request()->routeIs('customer.profile.current-booking')
                        ? 'bg-[#f8eee9] text-[#6d4033]'
                        : 'text-gray-600 hover:bg-[#fcf8f6] hover:text-[#6d4033]' }}"
                >

                    <span
                        class="flex h-10 w-10 items-center justify-center rounded-xl
                        {{ request()->routeIs('customer.profile.current-booking')
                            ? 'bg-white text-[#9a6252]'
                            : 'bg-[#faf7f5] text-gray-400' }}"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 7v5l3 2"/>
                        </svg>
                    </span>

                    الحجز الحالي

                </a>

            </nav>


            {{-- Logout --}}
            <div class="border-t border-gray-100 p-4">

                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="flex w-full items-center gap-3 rounded-2xl px-4 py-3.5 text-sm font-bold text-red-500 transition hover:bg-red-50"
                    >

                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-50"
                        >
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path d="M10 17l5-5-5-5"/>
                                <path d="M15 12H3"/>
                                <path d="M21 3v18"/>
                            </svg>
                        </span>

                        تسجيل الخروج

                    </button>

                </form>

            </div>

        </aside>


        {{-- ========================================================= --}}
        {{-- Main Area --}}
        {{-- ========================================================= --}}

        <div class="lg:pr-[280px]">

            {{-- Header --}}
            <header
                class="sticky top-0 z-30 flex h-20 items-center justify-between border-b border-[#eaded8] bg-[#faf8f6]/90 px-4 backdrop-blur-xl sm:px-6 lg:px-8"
            >

                <div class="flex items-center gap-3">

                    <button
                        type="button"
                        @click="sidebarOpen = true"
                        class="flex h-11 w-11 items-center justify-center rounded-xl border border-[#eaded8] bg-white text-[#6d4235] lg:hidden"
                    >
                        ☰
                    </button>

                    <div>

                        <p class="text-xs font-semibold text-gray-400">
                            مساحة العميل
                        </p>

                        <p class="text-sm font-bold text-[#4b2a22]">
                            أهلاً {{ $sidebarCustomer?->name }}
                        </p>

                    </div>

                </div>


                {{-- Quick profile --}}
                <a
                    href="{{ route('customer.profile') }}"
                    class="hidden items-center gap-3 rounded-2xl border border-[#eaded8] bg-white px-3 py-2 sm:flex"
                >

                    @if($sidebarCustomer?->avatar)

                        <img
                           src="{{ asset($sidebarCustomer->avatar) }}"
                            class="h-9 w-9 rounded-xl object-cover"
                            alt="{{ $sidebarCustomer->name }}"
                        >

                    @else

                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#f3e5de] text-xs font-bold text-[#7d4a3a]"
                        >
                            {{ strtoupper(mb_substr($sidebarCustomer?->name ?? 'U', 0, 1)) }}
                        </div>

                    @endif

                    <span class="text-sm font-bold text-[#4b2a22]">
                        حسابي
                    </span>

                </a>

            </header>


            {{-- Content --}}
            <main class="min-h-[calc(100vh-80px)]">
                {{ $slot }}
            </main>

        </div>

    </div>

    @livewireScripts

</body>
</html>