<?php

use App\Models\Booking;
use App\Models\Employee;
use App\Models\SubService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('حجوزاتي')] class extends Component
{
    public $customer;
public array $selectedBooking = [];

public function openBookingDetails(int $bookingId): void
{
    $booking = $this->todayBookings
        ->firstWhere('id', $bookingId);

    if (!$booking) {
        return;
    }

    $this->selectedBooking = [
        'id' => $booking->id,
        'date' => $booking->date,
        'time' => $booking->time,
        'employee_name' => $booking->employee_name ?? 'غير محدد',
        'service_names' => $booking->service_names ?? [],
        'turn' => (int) $booking->turn,
        'status' => $booking->status,
    ];
}

public function closeBookingDetails(): void
{
    $this->selectedBooking = [];
}
 
     public function mount(): void
    {
        $this->customer = Auth::guard('customer')->user();

        abort_unless($this->customer, 403);
    }
 
 
    public function getTodayBookingsProperty()
    {
        $bookings = Booking::query()
            ->where('user_id', $this->customer->id)
            ->whereDate('date', today())
            ->orderByRaw('CASE WHEN status = "completed" THEN 1 ELSE 0 END')
            ->orderBy('time')
            ->get();

        $employeeIds = $bookings
            ->pluck('employee_id')
            ->filter()
            ->unique();

        $employees = Employee::query()
            ->whereIn('id', $employeeIds)
            ->get()
            ->keyBy('id');

  $serviceIds = collect();

foreach ($bookings as $booking) {

    $storedServiceIds = $booking->getAttribute('service_ids');

    if (is_string($storedServiceIds)) {
        $decoded = json_decode($storedServiceIds, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $storedServiceIds = $decoded;
        }
    }

    if (is_array($storedServiceIds)) {
        $serviceIds = $serviceIds->merge($storedServiceIds);
    }

    if ($booking->service_id) {
        $serviceIds->push($booking->service_id);
    }
}

$serviceIds = $serviceIds
    ->filter()
    ->map(fn ($id) => (int) $id)
    ->unique()
    ->values();

$services = SubService::query()
    ->whereIn('id', $serviceIds)
    ->get()
    ->keyBy('id');

return $bookings->map(function (Booking $booking) use ($employees, $services) {

    $booking->employee_name =
        $employees->get($booking->employee_id)?->name
        ?? 'غير محدد';

    $storedServiceIds = $booking->getAttribute('service_ids');

    if (is_string($storedServiceIds)) {

        $decoded = json_decode($storedServiceIds, true);

        $storedServiceIds =
            json_last_error() === JSON_ERROR_NONE && is_array($decoded)
                ? $decoded
                : [];
    }

    if (!is_array($storedServiceIds)) {
        $storedServiceIds = [];
    }

    /*
    |--------------------------------------------------------------------------
    | New multi-service structure
    |--------------------------------------------------------------------------
    */

    $bookingServices = collect($storedServiceIds)
        ->map(fn ($id) => $services->get((int) $id))
        ->filter()
        ->values();

    /*
    |--------------------------------------------------------------------------
    | Old single-service structure
    |--------------------------------------------------------------------------
    */

    if ($bookingServices->isEmpty() && $booking->service_id) {

        $service = $services->get(
            (int) $booking->service_id
        );

        if ($service) {
            $bookingServices = collect([$service]);
        }
    }

    $booking->service_names = $bookingServices
        ->pluck('name')
        ->values()
        ->all();

    $booking->service_name =
        collect($booking->service_names)->first()
        ?? 'الخدمة';

    return $booking;
});
    }

    public function getCurrentBookingProperty(): ?Booking
    {
        return $this->todayBookings
            ->where('status', 'pending')
            ->filter(function (Booking $booking) {

                $bookingDateTime = Carbon::parse($booking->date)
                    ->setTimeFromTimeString($booking->time);

                return $bookingDateTime->greaterThanOrEqualTo(now());
            })
            ->sortBy('time')
            ->first();
    }

    public function refreshBookings(): void
    {
        //
    }
};
?>

<div
    wire:poll.15s="refreshBookings"
    dir="rtl"
    class="min-h-screen bg-[#faf8f6] py-8 sm:py-12"
>
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">

        <div class="mb-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

                <div>
                    <span class="inline-flex rounded-full bg-[#f3e5de] px-4 py-2 text-xs font-bold text-[#a56a58]">
                        حجوزاتي
                    </span>

                    <h1 class="mt-4 text-3xl font-bold tracking-tight text-[#4b2a22] sm:text-4xl">
                        حجوزات اليوم
                    </h1>

                    <p class="mt-2 text-sm leading-7 text-gray-500">
                        تابع موعدك، دورك، وحالة الحجز لحظة بلحظة.
                    </p>
                </div>

                <div class="inline-flex w-fit items-center gap-2 rounded-2xl border border-[#eaded8] bg-white px-4 py-3 text-sm font-semibold text-[#6d4235] shadow-sm">
                    📅
                    {{ now()->translatedFormat('l، d F Y') }}
                </div>

            </div>
        </div>


        @if($this->currentBooking)

            @php
                $current = $this->currentBooking;

                $appointmentAt = Carbon::parse($current->date)
                    ->setTimeFromTimeString($current->time);

                $appointmentIso = $appointmentAt->toIso8601String();

                $currentTurn = (int) $current->turn;
            @endphp

            <section class="relative mb-6 overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#5b3025] via-[#704234] to-[#9b6351] p-6 text-white shadow-[0_25px_70px_rgba(91,48,37,0.18)] sm:p-8">

                <div class="relative">

                    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

                        <div>
                            <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-xs font-bold text-white/80">
                                <span class="h-2 w-2 animate-pulse rounded-full bg-green-300"></span>
                                الحجز القادم
                            </div>

                            <h2 class="mt-4 text-2xl font-bold sm:text-3xl">
                                حجزك اليوم جاهز
                            </h2>

                            <p class="mt-2 text-sm leading-7 text-white/70">
                                تابع الوقت المتبقي ودورك في الطابور من هنا.
                            </p>
                        </div>


                        <div
                            x-data="{
                                target: new Date(@js($appointmentIso)).getTime(),
                                total: 0,

                                update() {
                                    this.total = Math.max(
                                        0,
                                        this.target - Date.now()
                                    );
                                },

                                format() {
                                    let seconds = Math.floor(this.total / 1000);

                                    let hours = Math.floor(seconds / 3600);

                                    seconds %= 3600;

                                    let minutes = Math.floor(seconds / 60);

                                    seconds %= 60;

                                    return {
                                        hours: String(hours).padStart(2, '0'),
                                        minutes: String(minutes).padStart(2, '0'),
                                        seconds: String(seconds).padStart(2, '0'),
                                    };
                                },

                                init() {
                                    this.update();

                                    setInterval(() => {
                                        this.update();
                                    }, 1000);
                                }
                            }"
                            class="shrink-0"
                        >

                            <div class="rounded-[1.75rem] border border-white/10 bg-black/10 px-5 py-4 backdrop-blur-sm">

                                <p class="text-center text-xs font-semibold text-white/60">
                                    الوقت المتبقي
                                </p>

                                <div class="mt-3 flex items-center gap-2">

                                    <div class="min-w-[58px] text-center">
                                        <div class="text-2xl font-bold" x-text="format().hours"></div>
                                        <div class="mt-1 text-[10px] text-white/50">
                                            ساعة
                                        </div>
                                    </div>

                                    <span class="text-xl text-white/40">:</span>

                                    <div class="min-w-[58px] text-center">
                                        <div class="text-2xl font-bold" x-text="format().minutes"></div>
                                        <div class="mt-1 text-[10px] text-white/50">
                                            دقيقة
                                        </div>
                                    </div>

                                    <span class="text-xl text-white/40">:</span>

                                    <div class="min-w-[58px] text-center">
                                        <div class="text-2xl font-bold" x-text="format().seconds"></div>
                                        <div class="mt-1 text-[10px] text-white/50">
                                            ثانية
                                        </div>
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <div class="mt-7 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">

                        <div class="rounded-2xl bg-white/10 px-4 py-4 backdrop-blur-sm">
                            <p class="text-xs text-white/50">
                                الوقت
                            </p>

                            <p class="mt-2 text-lg font-bold">
                                {{ Carbon::parse($current->time)->format('h:i A') }}
                            </p>
                        </div>


                        <div class="rounded-2xl bg-white/10 px-4 py-4 backdrop-blur-sm">
                            <p class="text-xs text-white/50">
                                الحلاق
                            </p>

                            <p class="mt-2 text-lg font-bold">
                                {{ $current->employee_name }}
                            </p>
                        </div>


                        <div class="rounded-2xl bg-white/10 px-4 py-4 backdrop-blur-sm">
                            <p class="text-xs text-white/50">
                                الدور
                            </p>

                            <p class="mt-2 text-lg font-bold">
                                {{ $currentTurn > 0 ? '#' . $currentTurn : '✓' }}
                            </p>
                        </div>


                        <div class="rounded-2xl bg-white/10 px-4 py-4 backdrop-blur-sm">
                         <div class="mt-2 flex flex-wrap gap-2">

    @forelse($current->service_names as $serviceName)

        <span
            class="rounded-full bg-white/10 px-3 py-1 text-sm font-bold text-white"
        >
            {{ $serviceName }}
        </span>

    @empty

        <span class="font-bold">
            الخدمة
        </span>

    @endforelse

</div>
                        </div>

                    </div>

                </div>

            </section>

        @endif


        <section class="mb-6 grid gap-4 sm:grid-cols-3">

            <div class="rounded-[1.5rem] border border-[#eaded8] bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">
                    حجوزات اليوم
                </p>

                <p class="mt-2 text-3xl font-bold text-[#4b2a22]">
                    {{ $this->todayBookings->count() }}
                </p>
            </div>


            <div class="rounded-[1.5rem] border border-[#eaded8] bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">
                    قيد الانتظار
                </p>

                <p class="mt-2 text-3xl font-bold text-amber-600">
                    {{ $this->todayBookings->where('status', 'pending')->count() }}
                </p>
            </div>


            <div class="rounded-[1.5rem] border border-[#eaded8] bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">
                    مكتملة
                </p>

                <p class="mt-2 text-3xl font-bold text-green-600">
                    {{ $this->todayBookings->where('status', 'completed')->count() }}
                </p>
            </div>

        </section>


        <section class="rounded-[2rem] border border-[#eaded8] bg-white p-6 shadow-sm sm:p-7">

            <div class="mb-6 flex items-center justify-between">

                <div>
                    <h2 class="text-xl font-bold text-[#4b2a22]">
                        تفاصيل الحجوزات
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        كل حجوزاتك المسجلة لليوم.
                    </p>
                </div>

                <span class="rounded-full bg-[#f8eee9] px-3 py-1.5 text-xs font-bold text-[#8c5747]">
                    {{ $this->todayBookings->count() }} حجز
                </span>

            </div>


            @if($this->todayBookings->isNotEmpty())

                <div class="space-y-4">

                    @foreach($this->todayBookings as $booking)

                        @php
                            $appointment = Carbon::parse($booking->date)
                                ->setTimeFromTimeString($booking->time);

                            $isCompleted = $booking->status === 'completed';

                            $isPending = $booking->status === 'pending';

                            $isCurrent =
                                $isPending &&
                                $appointment->greaterThanOrEqualTo(now());

                            $turn = (int) $booking->turn;
                        @endphp


                        <article
                            wire:key="booking-{{ $booking->id }}"
                            class="rounded-[1.5rem] border p-5 transition {{
                                $isCurrent
                                    ? 'border-[#d6b5a7] bg-[#fffaf8] shadow-sm'
                                    : 'border-gray-100 bg-white'
                            }}"
                        >

                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">

                                <div class="flex items-start gap-4">

                                    <div class="flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-2xl {{
                                        $isCompleted
                                            ? 'bg-green-50 text-green-700'
                                            : 'bg-[#f8eee9] text-[#8c5747]'
                                    }}">

                                        <span class="text-lg font-bold leading-none">
                                            {{ Carbon::parse($booking->time)->format('h:i') }}
                                        </span>

                                        <span class="mt-1 text-[10px] font-semibold">
                                            {{ Carbon::parse($booking->time)->format('A') }}
                                        </span>

                                    </div>


                                    <div>
<div class="flex flex-wrap items-center gap-2">

    @forelse($booking->service_names as $serviceName)

        <span
            class="rounded-full bg-[#f8eee9] px-3 py-1 text-xs font-bold text-[#7b4537]"
        >
            {{ $serviceName }}
        </span>

    @empty

        <span class="text-lg font-bold text-[#4b2a22]">
            الخدمة
        </span>

    @endforelse

</div>

                                            @if($isCurrent)

                                                <span class="rounded-full bg-green-50 px-2.5 py-1 text-[10px] font-bold text-green-700">
                                                    القادم
                                                </span>

                                            @elseif($isCompleted)

                                                <span class="rounded-full bg-green-50 px-2.5 py-1 text-[10px] font-bold text-green-700">
                                                    مكتمل
                                                </span>

                                            @else

                                                <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-bold text-amber-700">
                                                    قيد الانتظار
                                                </span>

                                            @endif

                                        </div>

                                        <p class="mt-2 text-sm text-gray-500">
                                            مع {{ $booking->employee_name }}
                                        </p>

                                    </div>

                                </div>


                                <div>

                         @if($isPending)

<button
    type="button"
    wire:click="openBookingDetails({{ $booking->id }})"
    class="inline-flex items-center gap-2 rounded-xl bg-[#5b3025] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#713c30]"
>
    تفاصيل الحجز
</button>

@elseif($isCompleted)

    <span
        class="inline-flex items-center gap-2 rounded-xl bg-green-50 px-4 py-2.5 text-sm font-bold text-green-700"
    >
        ✓ تمت الخدمة
    </span>

@endif

                                </div>

                            </div>


                            <div class="mt-5 grid gap-3 border-t border-gray-100 pt-5 sm:grid-cols-3">

                                <div class="rounded-xl bg-[#fcfaf9] px-4 py-3">
                                    <p class="text-[11px] text-gray-400">
                                        الحلاق
                                    </p>

                                    <p class="mt-1 text-sm font-bold text-gray-700">
                                        {{ $booking->employee_name }}
                                    </p>
                                </div>


                                <div class="rounded-xl bg-[#fcfaf9] px-4 py-3">
                                    <p class="text-[11px] text-gray-400">
                                        وقت الحجز
                                    </p>

                                    <p class="mt-1 text-sm font-bold text-gray-700">
                                        {{ Carbon::parse($booking->time)->format('h:i A') }}
                                    </p>
                                </div>


                                <div class="rounded-xl bg-[#fcfaf9] px-4 py-3">
                                    <p class="text-[11px] text-gray-400">
                                        رقم الدور
                                    </p>

                                    <p class="mt-1 text-sm font-bold text-gray-700">
                                        @if($turn > 0)
                                            #{{ $turn }}
                                        @else
                                            <span class="text-green-600">✓</span>
                                        @endif
                                    </p>
                                </div>

                            </div>


                            @if($isCurrent)

                                <div
                                    x-data="{
                                        target: new Date(@js($appointment->toIso8601String())).getTime(),
                                        remaining: 0,

                                        update() {
                                            this.remaining = Math.max(
                                                0,
                                                this.target - Date.now()
                                            );
                                        },

                                        format() {
                                            let seconds = Math.floor(this.remaining / 1000);

                                            let hours = Math.floor(seconds / 3600);

                                            seconds %= 3600;

                                            let minutes = Math.floor(seconds / 60);

                                            seconds %= 60;

                                            return {
                                                hours: String(hours).padStart(2, '0'),
                                                minutes: String(minutes).padStart(2, '0'),
                                                seconds: String(seconds).padStart(2, '0')
                                            };
                                        },

                                        init() {
                                            this.update();

                                            setInterval(() => {
                                                this.update();
                                            }, 1000);
                                        }
                                    }"
                                    class="mt-4 flex items-center justify-between rounded-2xl bg-[#f8eee9] px-4 py-3"
                                >

                                    <div>
                                        <p class="text-xs font-semibold text-[#9a6252]">
                                            موعدك بعد
                                        </p>

                                        <p class="mt-1 text-sm font-bold text-[#5b3025]">
                                            لا تتأخر عن الموعد
                                        </p>
                                    </div>

                                    <div
                                        dir="ltr"
                                        class="font-mono text-lg font-bold tracking-wider text-[#7b4537]"
                                        x-text="
                                            format().hours +
                                            ':' +
                                            format().minutes +
                                            ':' +
                                            format().seconds
                                        "
                                    ></div>

                                </div>

                            @endif

                        </article>

                    @endforeach

                </div>

            @else

                <div class="rounded-[1.5rem] border border-dashed border-[#decfc8] bg-[#fcfaf9] px-6 py-14 text-center">

                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[#f5e9e4] text-2xl">
                        📅
                    </div>

                    <h3 class="mt-5 text-xl font-bold text-[#4b2a22]">
                        مفيش حجوزات النهارده
                    </h3>

                    <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-gray-500">
                        لما تعمل حجز جديد، هيظهر هنا بكل تفاصيله وموعده.
                    </p>

                </div>

            @endif

        </section>

    </div>
@if($selectedBooking)

    @php
        $selectedAppointment = Carbon::parse($selectedBooking['date'])
            ->setTimeFromTimeString($selectedBooking['time']);
    @endphp
    <div
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
        wire:click.self="closeBookingDetails"
    >

        <div
            class="w-full max-w-lg overflow-hidden rounded-[2rem] bg-white shadow-2xl"
            wire:click.stop
        >

            <div class="bg-gradient-to-br from-[#5b3025] to-[#8c5747] px-6 py-6 text-white">

                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-xs font-semibold text-white/60">
                            تفاصيل الحجز
                        </p>

                        <h2 class="mt-2 text-2xl font-bold">
                            حجزك
                        </h2>
                    </div>

                    <button
                        type="button"
                        wire:click="closeBookingDetails"
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-xl transition hover:bg-white/20"
                    >
                        ×
                    </button>

                </div>

            </div>


            <div class="space-y-4 p-6">

                {{-- Date --}}
                <div class="flex items-center gap-4 rounded-2xl bg-[#fcfaf9] p-4">

                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#f5e9e4]">
                        📅
                    </div>

                    <div>
                        <p class="text-xs text-gray-400">
                            التاريخ
                        </p>

                        <p class="mt-1 font-bold text-[#4b2a22]">
                            {{ $selectedAppointment->translatedFormat('l، d F Y') }}
                        </p>
                    </div>

                </div>


                {{-- Time --}}
                <div class="flex items-center gap-4 rounded-2xl bg-[#fcfaf9] p-4">

                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#f5e9e4]">
                        🕐
                    </div>

                    <div>
                        <p class="text-xs text-gray-400">
                            الوقت
                        </p>

                        <p class="mt-1 font-bold text-[#4b2a22]">
                           {{ Carbon::parse($selectedBooking['time'])->format('h:i A') }}
                        </p>
                    </div>

                </div>


                {{-- Employee --}}
                <div class="flex items-center gap-4 rounded-2xl bg-[#fcfaf9] p-4">

                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#f5e9e4]">
                        👤
                    </div>

                    <div>
                        <p class="text-xs text-gray-400">
                            الحلاق
                        </p>

                        <p class="mt-1 font-bold text-[#4b2a22]">
                         {{ $selectedBooking['employee_name'] }}
                        </p>
                    </div>

                </div>


                {{-- Services --}}
                <div class="rounded-2xl bg-[#fcfaf9] p-4">

                    <div class="flex items-start gap-4">

                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#f5e9e4]">
                            ✂️
                        </div>

                        <div class="flex-1">

                            <p class="text-xs text-gray-400">
                                الخدمات
                            </p>

                            <div class="mt-2 flex flex-wrap gap-2">

                              @forelse($selectedBooking['service_names'] ?? [] as $serviceName)

                                    <span
                                        class="rounded-full bg-[#f5e9e4] px-3 py-1.5 text-xs font-bold text-[#7b4537]"
                                    >
                                        {{ $serviceName }}
                                    </span>

                                @empty

                                    <span class="font-bold text-[#4b2a22]">
                                        الخدمة
                                    </span>

                                @endforelse

                            </div>

                        </div>

                    </div>

                </div>


                {{-- Turn + Status --}}
                <div class="grid grid-cols-2 gap-4">

                    <div class="rounded-2xl bg-[#fcfaf9] p-4">

                        <p class="text-xs text-gray-400">
                            رقم الدور
                        </p>

                        <p class="mt-2 text-xl font-bold text-[#4b2a22]">

                           @if((int) $selectedBooking['turn'] > 0)

                            #{{ $selectedBooking['turn'] }}

                            @else

                                <span class="text-green-600">
                                    ✓
                                </span>

                            @endif

                        </p>

                    </div>


                    <div class="rounded-2xl bg-[#fcfaf9] p-4">

                        <p class="text-xs text-gray-400">
                            الحالة
                        </p>

                        <div class="mt-2">

                           @if($selectedBooking['status'] === 'pending')

                                <span class="rounded-full bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">
                                    قيد الانتظار
                                </span>

                            @elseif($selectedBooking['status'] === 'completed')

                                <span class="rounded-full bg-green-50 px-3 py-1.5 text-xs font-bold text-green-700">
                                    مكتمل
                                </span>

                            @else

                                <span class="rounded-full bg-gray-100 px-3 py-1.5 text-xs font-bold text-gray-600">
                                    {{ $selectedBooking['status'] }}
                                </span>

                            @endif

                        </div>

                    </div>

                </div>

            </div>


            <div class="border-t border-gray-100 bg-[#fcfaf9] p-5">

                <button
                    type="button"
                    wire:click="closeBookingDetails"
                    class="w-full rounded-2xl bg-[#5b3025] px-5 py-3.5 text-sm font-bold text-white transition hover:bg-[#713c30]"
                >
                    إغلاق
                </button>

            </div>

        </div>

    </div>

@endif
</div>