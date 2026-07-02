@extends('web.layouts.app')
@section('content')

 <x-sections.landing />
 <x-sections.about />
 <x-sections.divider />
 <x-sections.services />
 <x-sections.review />
 <x-sections.prices />
 <x-sections.booking />
 <section id="locations" class="w-full bg-white py-24">

    <div class="mx-auto max-w-7xl px-6">

        <div class="mb-16 text-center">

            <h2 class="heading-font text-4xl text-[#5d3026] sm:text-5xl">
                فروعنا المختارة
            </h2>

            <p class="mt-3 text-sm uppercase tracking-[4px] text-[#c98a6a]">
                تواجدنا في قلب المدينة
            </p>

        </div>

        <div class="flex flex-wrap items-center justify-center content-center">

            <div class="text-center">

                <img
                    src="{{ asset('images/about3.png') }}"
                    class="mx-auto h-52 w-52 rounded-full object-cover"
                >

                <h3 class="mt-8 text-2xl text-[#5d3026] sm:text-3xl">
                    فرع الزمالك • ٤٥٠٢ شارع الرئيسي
                </h3>

                <p class="mt-4 text-sm text-gray-700">
                    ٠١٠٠٠٠٠٠٠٠٠
                </p>

                <p class="mt-2 text-sm text-gray-700">
                    الإثنين - السبت • ١٠ صباحًا - ٧ مساءً
                </p>

                <a
                    href="#"
                    class="mt-8 inline-block bg-[#5d3026] px-8 py-3 text-white transition hover:bg-[#c98a6a]">
                    اكتشف الموقع
                </a>

            </div>

        </div>

    </div>

</section>

<section
    id="news"
    class="relative h-[500px] w-full bg-fixed bg-center bg-cover"
    style="background-image:url('{{ asset('images/15.jpg') }}')">

    <div class="absolute inset-0 bg-black/40"></div>

    <div class="relative flex h-full flex-col items-center justify-center text-center text-white">

        <h2 class="heading-font mb-8 text-4xl sm:text-6xl">
            اكتشف عالمنا الراقِي
        </h2>

        <p class="mb-8 max-w-2xl px-6 text-lg text-white/90">
            من العناية باللحية إلى أحدث تصاميم التصفيف، كل التفاصيل مصممة لتمنحك مظهرًا أنيقًا وثقة بصريّة لا تُنسى.
        </p>

        <a
            href="#"
            class="bg-[#5d3026] px-10 py-4 uppercase tracking-widest transition hover:bg-[#c98a6a]">
            تفضّل بزيارة المتجر
        </a>

    </div>

</section>

@endsection


 