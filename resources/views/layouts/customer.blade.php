<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        {{ $title ?? 'Customer' }}
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body class="min-h-screen bg-[#faf8f6] text-gray-900">

    {{-- Navbar --}}
    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">

            <a
                href="{{ url('/') }}"
                class="text-xl font-bold tracking-tight"
            >
                Beauty Salon
            </a>

            <div class="flex items-center gap-5">

                {{-- Notifications --}}
                <a
                    href="#"
                    class="relative text-gray-600 transition hover:text-black"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M14.857 17.082a23.848 23.848 0 0 1-5.714 0M18.75 10.5a6.75 6.75 0 0 0-13.5 0c0 7.875-3.375 7.875-3.375 7.875h20.25s-3.375 0-3.375-7.875Z"
                        />
                    </svg>

                    {{-- Notification badge --}}
                    <span
                        class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-black px-1 text-[10px] text-white"
                    >
                        3
                    </span>
                </a>

                {{-- Customer --}}
                <a
                    href="{{ route('customer.profile.index') }}"
                    class="flex items-center gap-3"
                >

                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-900 text-sm font-semibold text-white"
                    >
                        {{ strtoupper(substr(auth('customer')->user()->name, 0, 1)) }}
                    </div>

                    <div class="hidden sm:block">
                        <p class="text-sm font-semibold">
                            {{ auth('customer')->user()->name }}
                        </p>

                        <p class="text-xs text-gray-500">
                            My Account
                        </p>
                    </div>

                </a>

            </div>
        </div>
    </header>


    {{-- Main --}}
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

        {{ $slot }}

    </main>


    @livewireScripts

</body>
</html>