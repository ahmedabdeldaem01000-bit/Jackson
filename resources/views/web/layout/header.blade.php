<nav id="navbar"
 {{ request()->routeIs('login', 'register') ? 'bg-white shadow-lg' : 'bg-transparent' }}
     class="fixed top-0 left-0 z-50 w-full transition-all duration-500">

    <div
        class="max-w-[1400px] mx-auto flex items-center justify-between px-6 lg:px-12 py-7 transition-all duration-500 navbar-container">

        <a href="#" class="shrink-0">

            <img
                src="{{ asset('images/Untitled-4.png') }}"
                class="h-14 lg:h-20 w-auto transition-all duration-500 logo"
                alt="Logo">

        </a>
<ul
    id="list"
    class="hidden lg:flex items-center gap-8 text-sm font-semibold tracking-[2px]
           {{ request()->routeIs('login', 'register') ? 'text-black' : 'text-white' }}
           transition-all duration-300"
>

    <li><a href="{{ route('home') }}">الرئيسية</a></li>
    <li><a href="#about">من نحن</a></li>
    <li><a href="#services">خدماتنا</a></li>
    <li><a href="#booking">الحجز</a></li>
    <li><a href="#prices">الأسعار</a></li>
    <li><a href="#locations">الفروع</a></li>
    <li><a href="#contact">تواصل معنا</a></li>

    @auth('employee')

        <li>
            <form action="{{ route('employee.logout') }}" method="POST">
                @csrf
                <button type="submit">
                    تسجيل الخروج
                </button>
            </form>
        </li>

    @elseif(auth()->check())

        <li>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit">
                    تسجيل الخروج
                </button>
            </form>
        </li>

    @else

        <li>
            <a href="{{ route('login') }}">
                تسجيل الدخول
            </a>
        </li>

    @endauth

</ul>

        <div class="flex items-center gap-5">

            <a href="#booking"
                class="hidden lg:flex rounded bg-[#5b3025] px-6 py-3 text-white uppercase tracking-wider transition hover:bg-[#734034]">
                احجز الآن
            </a>

            <button
                id="menuBtn"
                class="text-4xl text-white lg:hidden">
                ☰
            </button>

        </div>

    </div>

    <div
        id="mobileMenu"
        class="hidden bg-white shadow-xl lg:hidden">

        <ul class="flex flex-col text-right font-semibold uppercase">

            <li><a class="block px-6 py-4" href="#">الرئيسية</a></li>
            <li><a class="block px-6 py-4" href="#about">من نحن</a></li>
            <li><a class="block px-6 py-4" href="#services">خدماتنا</a></li>
            <li><a class="block px-6 py-4" href="#booking">الحجز</a></li>
            <li><a class="block px-6 py-4" href="#prices">الأسعار</a></li>
            <li><a class="block px-6 py-4" href="#locations">الفروع</a></li>
            <li><a class="block px-6 py-4" href="#news">الأخبار</a></li>
            <li><a class="block px-6 py-4" href="#contact">تواصل معنا</a></li>

        </ul>

    </div>

</nav>