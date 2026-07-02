<section id="prices" class="w-full bg-white py-24">

    <div class="mx-auto max-w-6xl px-6">

        <div class="text-center">

            <h2 class="heading-font text-4xl text-[#5b3025] sm:text-5xl">
                الأسعار المخصصة لأسلوبك
            </h2>

            <p class="mt-2 text-sm uppercase tracking-[4px] text-[#c98a6a]">
                اختَر الخدمة الأنسب لك
            </p>

        </div>

        <div class="mt-14 flex flex-wrap justify-center gap-4">

            <button
                class="price-tab active"
                data-tab="shaves">

                <img src="{{ asset('images/1.jpg') }}" alt="">

                <span>الحلاقـة الساخنة</span>

            </button>

            <button
                class="price-tab"
                data-tab="haircuts">

                <img src="{{ asset('images/2.jpg') }}" alt="">

                <span>التصفيف والقص</span>

            </button>

            <button
                class="price-tab"
                data-tab="trims">

                <img src="{{ asset('images/9.png') }}" alt="">

                <span>تقليم اللحية</span>

            </button>

        </div>

        <div class="mx-auto mt-20 max-w-2xl">

            <h3
                id="priceTitle"
                class="mb-14 text-center text-4xl text-[#c98a6a] sm:text-6xl">

                الحلاقة الساخنة

            </h3>

            <div id="priceList">

            </div>

        </div>

    </div>

</section>