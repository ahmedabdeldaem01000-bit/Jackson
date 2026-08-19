<?php

use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('الملف الشخصي')] class extends Component
{
    public $customer;

    public function mount(): void
    {
        $this->customer = Auth::guard('customer')->user();

        abort_unless($this->customer, 403);
    }

    public function getTotalBookingsProperty(): int
    {
        return Booking::query()
            ->where('user_id', $this->customer->id)
            ->count();
    }

    public function getPendingBookingsProperty(): int
    {
        return Booking::query()
            ->where('user_id', $this->customer->id)
            ->where('status', 'pending')
            ->count();
    }

    public function getCompletedBookingsProperty(): int
    {
        return Booking::query()
            ->where('user_id', $this->customer->id)
            ->where('status', 'completed')
            ->count();
    }

    public function getLatestBookingProperty(): ?Booking
    {
        return Booking::query()
            ->where('user_id', $this->customer->id)
            ->latest('created_at')
            ->first();
    }
};
?>

<div
    dir="rtl"
    class="min-h-screen bg-[#faf8f6] py-8 sm:py-12"
>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        {{-- ========================================================= --}}
        {{-- Header --}}
        {{-- ========================================================= --}}

        <div class="mb-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

                <div>
                    <span
                        class="inline-flex items-center rounded-full bg-[#f3e5de] px-4 py-2 text-xs font-bold text-[#a56a58]"
                    >
                        حسابي
                    </span>

                    <h1
                        class="mt-4 text-3xl font-bold tracking-tight text-[#4b2a22] sm:text-4xl"
                    >
                        الملف الشخصي
                    </h1>

                    <p class="mt-2 text-sm leading-7 text-gray-500">
                        من هنا تقدر تتابع بيانات حسابك وحجوزاتك وإشعاراتك.
                    </p>
                </div>

                <div class="flex items-center gap-3">

                    <a
                        href="{{ route('customer.profile.information') }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-[#e8d8d0] bg-white px-4 py-2.5 text-sm font-semibold text-[#6d4235] shadow-sm transition hover:bg-[#fffaf8]"
                    >
                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/>
                            <path d="M4 20a8 8 0 0 1 16 0"/>
                        </svg>

                        تعديل البيانات
                    </a>

                </div>

            </div>
        </div>


        {{-- ========================================================= --}}
        {{-- Main Grid --}}
        {{-- ========================================================= --}}

        <div class="grid gap-6 lg:grid-cols-12">

     


            {{-- ===================================================== --}}
            {{-- Main Content --}}
            {{-- ===================================================== --}}

            <main class="space-y-6 lg:col-span-8">

                {{-- Welcome --}}
                <section
                    class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#5b3025] to-[#8c5747] p-7 text-white shadow-[0_20px_60px_rgba(91,48,37,0.16)] sm:p-8"
                >

                    <div
                        class="absolute -left-16 -top-20 h-56 w-56 rounded-full bg-white/10 blur-3xl"
                    ></div>

                    <div
                        class="absolute -bottom-20 -right-10 h-52 w-52 rounded-full bg-white/10 blur-3xl"
                    ></div>

                    <div class="relative">

                        <p class="text-sm font-medium text-white/70">
                            أهلاً بيك 👋
                        </p>

                        <h2 class="mt-2 text-2xl font-bold sm:text-3xl">
                            {{ $customer->name }}
                        </h2>

                        <p class="mt-3 max-w-xl text-sm leading-7 text-white/75">
                            كل حاجة تخص حسابك وحجوزاتك موجودة هنا في مكان واحد.
                        </p>

                    </div>

                </section>

                @if($customer->avatar)
    <img
          src="{{ asset( $customer->avatar) }}"
        alt="{{ $customer->name }}"
        class="h-20 w-20 rounded-3xl object-cover"
    >
@else
    <div
        class="flex h-20 w-20 items-center justify-center rounded-3xl bg-[#f5e9e4] text-3xl font-bold text-[#7d4a3a]"
    >
        {{ strtoupper(mb_substr($customer->name ?? 'U', 0, 1)) }}
    </div>
@endif

                {{-- ================================================= --}}
                {{-- Stats --}}
                {{-- ================================================= --}}

                <section class="grid gap-4 sm:grid-cols-3">

                    {{-- Total --}}
                    <div
                        class="rounded-[1.5rem] border border-[#eaded8] bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-center justify-between">

                            <div>
                                <p class="text-sm text-gray-500">
                                    إجمالي الحجوزات
                                </p>

                                <p class="mt-2 text-3xl font-bold text-[#4b2a22]">
                                    {{ $this->totalBookings }}
                                </p>
                            </div>

                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#f8eee9] text-[#9a6252]"
                            >
                                <svg
                                    class="h-6 w-6"
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
                            </div>

                        </div>
                    </div>


                    {{-- Pending --}}
                    <div
                        class="rounded-[1.5rem] border border-[#eaded8] bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-center justify-between">

                            <div>
                                <p class="text-sm text-gray-500">
                                    الحجوزات القادمة
                                </p>

                                <p class="mt-2 text-3xl font-bold text-[#4b2a22]">
                                    {{ $this->pendingBookings }}
                                </p>
                            </div>

                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600"
                            >
                                <svg
                                    class="h-6 w-6"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <circle cx="12" cy="12" r="9"/>
                                    <path d="M12 7v5l3 2"/>
                                </svg>
                            </div>

                        </div>
                    </div>


                    {{-- Completed --}}
                    <div
                        class="rounded-[1.5rem] border border-[#eaded8] bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-center justify-between">

                            <div>
                                <p class="text-sm text-gray-500">
                                    الحجوزات المكتملة
                                </p>

                                <p class="mt-2 text-3xl font-bold text-[#4b2a22]">
                                    {{ $this->completedBookings }}
                                </p>
                            </div>

                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-green-50 text-green-600"
                            >
                                <svg
                                    class="h-6 w-6"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <circle cx="12" cy="12" r="9"/>
                                    <path d="m8 12 2.5 2.5L16 9"/>
                                </svg>
                            </div>

                        </div>
                    </div>

                </section>


                {{-- ================================================= --}}
                {{-- Latest Booking --}}
                {{-- ================================================= --}}

                <section
                    class="rounded-[2rem] border border-[#eaded8] bg-white p-6 shadow-sm sm:p-7"
                >

                    <div class="flex items-center justify-between gap-4">

                        <div>
                            <h3 class="text-xl font-bold text-[#4b2a22]">
                                آخر حجز
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                آخر نشاط ظاهر على حسابك.
                            </p>
                        </div>

                        <a
                            href="{{ route('customer.profile.bookings') }}"
                            class="text-sm font-bold text-[#9a6252] transition hover:text-[#6d4033]"
                        >
                            عرض كل الحجوزات
                        </a>

                    </div>


                    @if($this->latestBooking)

                        @php
                            $booking = $this->latestBooking;
                        @endphp

                        <div
                            class="mt-6 rounded-2xl border border-[#eee2dc] bg-[#fcfaf9] p-5"
                        >

                            <div class="grid gap-5 sm:grid-cols-3">

                                {{-- Date --}}
                                <div>
                                    <p class="text-xs font-semibold text-gray-400">
                                        التاريخ
                                    </p>

                                    <p class="mt-2 font-bold text-[#4b2a22]">
                                        {{ $booking->date }}
                                    </p>
                                </div>


                                {{-- Time --}}
                                <div>
                                    <p class="text-xs font-semibold text-gray-400">
                                        الوقت
                                    </p>

                                    <p class="mt-2 font-bold text-[#4b2a22]">
                                        {{ $booking->time }}
                                    </p>
                                </div>


                                {{-- Status --}}
                                <div>
                                    <p class="text-xs font-semibold text-gray-400">
                                        الحالة
                                    </p>

                                    @if($booking->status === 'completed')

                                        <span
                                            class="mt-2 inline-flex items-center rounded-full bg-green-50 px-3 py-1.5 text-xs font-bold text-green-700"
                                        >
                                            مكتمل
                                        </span>

                                    @elseif($booking->status === 'pending')

                                        <span
                                            class="mt-2 inline-flex items-center rounded-full bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700"
                                        >
                                            قيد الانتظار
                                        </span>

                                    @else

                                        <span
                                            class="mt-2 inline-flex items-center rounded-full bg-gray-100 px-3 py-1.5 text-xs font-bold text-gray-600"
                                        >
                                            {{ $booking->status }}
                                        </span>

                                    @endif

                                </div>

                            </div>

                        </div>

                    @else

                        <div
                            class="mt-6 rounded-2xl border border-dashed border-[#decfc8] bg-[#fcfaf9] px-6 py-12 text-center"
                        >

                            <div
                                class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[#f5e9e4] text-[#9a6252]"
                            >
                                <svg
                                    class="h-7 w-7"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
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
                            </div>

                            <h4 class="mt-5 text-lg font-bold text-[#4b2a22]">
                                لسه مفيش حجوزات
                            </h4>

                            <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-gray-500">
                                أول ما تعمل حجز هيظهر هنا وتقدر تتابع حالته من صفحة حجوزاتي.
                            </p>

                        </div>

                    @endif

                </section>


                {{-- ================================================= --}}
                {{-- Quick Links --}}
                {{-- ================================================= --}}

                
            </main>

        </div>

    </div>
</div>