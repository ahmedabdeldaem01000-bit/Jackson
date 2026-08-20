<?php

use App\Models\Booking;
use App\Models\Employee;
use App\Models\SubService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new
    #[Layout('layouts.customer')]
    #[Title('حجوزاتي')]
class extends Component
{
    use WithPagination;

    /*
    |--------------------------------------------------------------------------
    | Customer
    |--------------------------------------------------------------------------
    */

    public $customer;


    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    public string $statusFilter = 'all';


    /*
    |--------------------------------------------------------------------------
    | Details Modal
    |--------------------------------------------------------------------------
    */

    public array $selectedBooking = [];


    /*
    |--------------------------------------------------------------------------
    | Mount
    |--------------------------------------------------------------------------
    */

    public function mount(): void
    {
        $this->customer = Auth::guard('customer')->user();

        abort_unless($this->customer, 403);
    }


    /*
    |--------------------------------------------------------------------------
    | Filter
    |--------------------------------------------------------------------------
    */

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }


    /*
    |--------------------------------------------------------------------------
    | Bookings
    |--------------------------------------------------------------------------
    */

    public function getBookingsProperty()
    {
       $bookings = Booking::query()
    ->with([
        'services:id,name',
        'employee:id,name',
    ])
    ->where('user_id', $this->customer->id)
    ->when(
        $this->statusFilter !== 'all',
        fn ($query) => $query->where(
            'status',
            $this->statusFilter
        )
    )
    ->orderByDesc('date')
    ->orderByDesc('time')
    ->paginate(10);


        /*
        |--------------------------------------------------------------------------
        | Employees
        |--------------------------------------------------------------------------
        */

        $employeeIds = $bookings->getCollection()
            ->pluck('employee_id')
            ->filter()
            ->unique()
            ->values();

        $employees = Employee::query()
            ->whereIn('id', $employeeIds)
            ->get()
            ->keyBy('id');


        /*
        |--------------------------------------------------------------------------
        | Collect Service IDs
        |--------------------------------------------------------------------------
        */

        $serviceIds = collect();


        foreach ($bookings->getCollection() as $booking) {

            /*
            |--------------------------------------------------------------------------
            | New structure: service_ids
            |--------------------------------------------------------------------------
            */

            $storedServiceIds = $booking->getAttribute('service_ids');

            if (is_string($storedServiceIds)) {

                $decoded = json_decode(
                    $storedServiceIds,
                    true
                );

                if (
                    json_last_error() === JSON_ERROR_NONE
                    && is_array($decoded)
                ) {
                    $storedServiceIds = $decoded;
                } else {
                    $storedServiceIds = [];
                }
            }


            if (is_array($storedServiceIds)) {

                $serviceIds = $serviceIds->merge(
                    $storedServiceIds
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Old structure: service_id
            |--------------------------------------------------------------------------
            */

            if ($booking->service_id) {

                $serviceIds->push(
                    $booking->service_id
                );
            }
        }


        $serviceIds = $serviceIds
            ->filter()
            ->map(
                fn ($id) => (int) $id
            )
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Services
        |--------------------------------------------------------------------------
        */

        $services = SubService::query()
            ->whereIn('id', $serviceIds)
            ->get()
            ->keyBy('id');


        /*
        |--------------------------------------------------------------------------
        | Prepare Bookings
        |--------------------------------------------------------------------------
        */

        $bookings->getCollection()->transform(
            function (Booking $booking) use (
                $employees,
                $services
            ) {

                /*
                |--------------------------------------------------------------------------
                | Employee Name
                |--------------------------------------------------------------------------
                */

                $booking->employee_name =
                    $employees
                        ->get($booking->employee_id)
                        ?->name
                    ?? 'غير محدد';


                /*
                |--------------------------------------------------------------------------
                | Read service_ids
                |--------------------------------------------------------------------------
                */

                $storedServiceIds =
                    $booking->getAttribute('service_ids');


                if (is_string($storedServiceIds)) {

                    $decoded = json_decode(
                        $storedServiceIds,
                        true
                    );

                    if (
                        json_last_error() === JSON_ERROR_NONE
                        && is_array($decoded)
                    ) {
                        $storedServiceIds = $decoded;
                    } else {
                        $storedServiceIds = [];
                    }
                }


                if (!is_array($storedServiceIds)) {
                    $storedServiceIds = [];
                }


                /*
                |--------------------------------------------------------------------------
                | Multi Services
                |--------------------------------------------------------------------------
                */

                $bookingServices = collect(
                    $storedServiceIds
                )
                    ->map(
                        fn ($id) =>
                            $services->get((int) $id)
                    )
                    ->filter()
                    ->values();


                /*
                |--------------------------------------------------------------------------
                | Fallback to service_id
                |--------------------------------------------------------------------------
                */

                if (
                    $bookingServices->isEmpty()
                    && $booking->service_id
                ) {

                    $service = $services->get(
                        (int) $booking->service_id
                    );

                    if ($service) {

                        $bookingServices = collect([
                            $service
                        ]);
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Service Names
                |--------------------------------------------------------------------------
                */

                $booking->service_names =
                    $bookingServices
                        ->pluck('name')
                        ->values()
                        ->all();


                /*
                |--------------------------------------------------------------------------
                | First Service Name
                |--------------------------------------------------------------------------
                */

                $booking->service_name =
                    $bookingServices
                        ->pluck('name')
                        ->first()
                    ?? 'غير محددة';


                return $booking;
            }
        );


        return $bookings;
    }


    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */

    public function getTotalBookingsProperty(): int
    {
        return Booking::query()
            ->where(
                'user_id',
                $this->customer->id
            )
            ->count();
    }


    public function getPendingBookingsProperty(): int
    {
        return Booking::query()
            ->where(
                'user_id',
                $this->customer->id
            )
            ->where(
                'status',
                'pending'
            )
            ->whereDate(
                'date',
                today()
            )
            ->count();
    }


    public function getCompletedBookingsProperty(): int
    {
        return Booking::query()
            ->where(
                'user_id',
                $this->customer->id
            )
            ->where(
                'status',
                'completed'
            )
            ->count();
    }


    /*
    |--------------------------------------------------------------------------
    | Details Modal
    |--------------------------------------------------------------------------
    */

  public function openDetails(int $bookingId): void
{
    $booking = Booking::query()
        ->with([
            'employee:id,name',
            'services:id,name',
        ])
        ->where('id', $bookingId)
        ->where('user_id', $this->customer->id)
        ->first();

    if (!$booking) {
        return;
    }

    $this->selectedBooking = [
        'id' => $booking->id,

        'date' => $booking->date,

        'time' => $booking->time,

        'employee_name' =>
            $booking->employee?->name
            ?? 'غير محدد',

        'service_names' =>
            $booking->services
                ->pluck('name')
                ->values()
                ->all(),

        'turn' => (int) $booking->turn,

        'status' => $booking->status,
    ];
}


    public function closeDetails(): void
    {
        $this->selectedBooking = [];
    }
};
?>

<div class="px-4 py-8 sm:px-6 lg:px-8">

    <div class="mx-auto max-w-7xl">

        {{-- ========================================================= --}}
        {{-- Header --}}
        {{-- ========================================================= --}}

        <div class="mb-8">

            <div
                class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between"
            >

                <div>

                    <span
                        class="inline-flex rounded-full bg-[#f3e5de] px-4 py-2 text-xs font-bold text-[#a56a58]"
                    >
                        سجل الحجوزات
                    </span>


                    <h1
                        class="mt-4 text-3xl font-bold tracking-tight text-[#4b2a22]"
                    >
                        حجوزاتي
                    </h1>


                    <p class="mt-2 text-sm leading-7 text-gray-500">
                        تابع جميع الحجوزات الخاصة بحسابك.
                    </p>

                </div>


                <div
                    class="inline-flex w-fit items-center gap-2 rounded-2xl border border-[#eaded8] bg-white px-4 py-3 shadow-sm"
                >

                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#f8eee9]"
                    >
                        📅
                    </span>

                    <div>

                        <p class="text-[11px] text-gray-400">
                            إجمالي الحجوزات
                        </p>

                        <p class="text-sm font-bold text-[#4b2a22]">
                            {{ $this->totalBookings }} حجز
                        </p>

                    </div>

                </div>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- Statistics --}}
        {{-- ========================================================= --}}

        <div class="mb-6 grid gap-4 sm:grid-cols-3">

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
                        📋
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
                            حجوزات اليوم
                        </p>

                        <p class="mt-2 text-3xl font-bold text-amber-600">
                            {{ $this->pendingBookings }}
                        </p>

                    </div>

                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600"
                    >
                        ⏳
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

                        <p class="mt-2 text-3xl font-bold text-green-600">
                            {{ $this->completedBookings }}
                        </p>

                    </div>

                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-green-50 text-green-600"
                    >
                        ✓
                    </div>

                </div>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- Filters --}}
        {{-- ========================================================= --}}

        <div
            class="mb-5 flex flex-col gap-4 rounded-[1.5rem] border border-[#eaded8] bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between"
        >

            <div>

                <h2 class="font-bold text-[#4b2a22]">
                    قائمة الحجوزات
                </h2>

                <p class="mt-1 text-xs text-gray-400">
                    استخدم الفلتر لعرض نوع معين من الحجوزات.
                </p>

            </div>


            <div class="flex flex-wrap gap-2">

                @foreach([
                    'all' => 'الكل',
                    'pending' => 'القادمة',
                    'completed' => 'المكتملة',
                    'cancelled' => 'الملغية',
                ] as $value => $label)

                    <button
                        type="button"
                        wire:click="$set('statusFilter', '{{ $value }}')"
                        class="rounded-xl px-4 py-2.5 text-sm font-bold transition
                        {{
                            $statusFilter === $value
                                ? 'bg-[#5b3025] text-white shadow-sm'
                                : 'bg-[#f8eee9] text-[#6d4235] hover:bg-[#f1e5df]'
                        }}"
                    >
                        {{ $label }}
                    </button>

                @endforeach

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- Table --}}
        {{-- ========================================================= --}}

        <div
            class="overflow-hidden rounded-[2rem] border border-[#eaded8] bg-white shadow-sm"
        >

            <div class="overflow-x-auto">

                <table class="w-full min-w-[1000px] text-right">

                    {{-- Header --}}
                    <thead
                        class="border-b border-[#eaded8] bg-[#fcfaf9]"
                    >

                        <tr>

                            <th
                                class="px-6 py-4 text-xs font-bold text-gray-400"
                            >
                                التاريخ
                            </th>

                            <th
                                class="px-6 py-4 text-xs font-bold text-gray-400"
                            >
                                الوقت
                            </th>

                            <th
                                class="px-6 py-4 text-xs font-bold text-gray-400"
                            >
                                الخدمات
                            </th>

                            <th
                                class="px-6 py-4 text-xs font-bold text-gray-400"
                            >
                                الحلاق
                            </th>

                            <th
                                class="px-6 py-4 text-xs font-bold text-gray-400"
                            >
                                الدور
                            </th>

                            <th
                                class="px-6 py-4 text-xs font-bold text-gray-400"
                            >
                                الحالة
                            </th>

                            <th
                                class="px-6 py-4 text-xs font-bold text-gray-400"
                            >
                                الإجراء
                            </th>

                        </tr>

                    </thead>


                    {{-- Body --}}
                    <tbody class="divide-y divide-gray-100">

                        @forelse($this->bookings as $booking)

                            @php

                                $bookingDate = Carbon::parse(
                                    $booking->date
                                );

                                $bookingTime = Carbon::parse(
                                    $booking->time
                                );

                            @endphp


                            <tr
                                wire:key="booking-{{ $booking->id }}"
                                class="transition hover:bg-[#fffaf8]"
                            >

                                {{-- Date --}}
                                <td class="px-6 py-5">

                                    <div
                                        class="font-bold text-[#4b2a22]"
                                    >
                                        {{ $bookingDate->format('d/m/Y') }}
                                    </div>

                                    <div
                                        class="mt-1 text-xs text-gray-400"
                                    >
                                        {{ $bookingDate->translatedFormat('l') }}
                                    </div>

                                </td>


                                {{-- Time --}}
                                <td class="px-6 py-5">

                                    <span
                                        class="inline-flex rounded-xl bg-[#f8eee9] px-3 py-2 text-sm font-bold text-[#7b4537]"
                                    >
                                        {{ $bookingTime->format('h:i A') }}
                                    </span>

                                </td>


                                {{-- Services --}}
                             <td class="px-6 py-5">
    <div class="flex max-w-[280px] flex-wrap gap-1.5">

        @forelse($booking->services as $service)

            <span
                class="rounded-full bg-[#f8eee9] px-2.5 py-1 text-xs font-bold text-[#7b4537]"
            >
                {{ $service->name }}
            </span>

        @empty

            <span class="text-sm text-gray-400">
                غير محددة
            </span>

        @endforelse

    </div>
</td>


                                {{-- Employee --}}
                           <td class="px-6 py-5">
    <span class="font-semibold text-gray-700">
        {{ $booking->employee?->name ?? 'غير محدد' }}
    </span>
</td>


                                {{-- Turn --}}
                                <td class="px-6 py-5">

                                    @if((int) $booking->turn > 0)

                                        <span
                                            class="inline-flex h-9 min-w-9 items-center justify-center rounded-xl bg-[#f8eee9] px-2 text-sm font-bold text-[#7b4537]"
                                        >
                                            #{{ $booking->turn }}
                                        </span>

                                    @else

                                        <span
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-green-50 text-green-600"
                                        >
                                            ✓
                                        </span>

                                    @endif

                                </td>


                                {{-- Status --}}
                                <td class="px-6 py-5">

                                    @if($booking->status === 'pending')

                                        <span
                                            class="inline-flex rounded-full bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700"
                                        >
                                            قيد الانتظار
                                        </span>

                                    @elseif($booking->status === 'completed')

                                        <span
                                            class="inline-flex rounded-full bg-green-50 px-3 py-1.5 text-xs font-bold text-green-700"
                                        >
                                            مكتملة
                                        </span>

                                    @elseif($booking->status === 'cancelled')

                                        <span
                                            class="inline-flex rounded-full bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700"
                                        >
                                            ملغية
                                        </span>

                                    @else

                                        <span
                                            class="inline-flex rounded-full bg-gray-100 px-3 py-1.5 text-xs font-bold text-gray-600"
                                        >
                                            {{ $booking->status }}
                                        </span>

                                    @endif

                                </td>


                                {{-- Action --}}
                                <td class="px-6 py-5">

                                    <button
                                        type="button"
                                        wire:click="openDetails({{ $booking->id }})"
                                        class="inline-flex items-center gap-2 rounded-xl border border-[#dfcec6] bg-white px-4 py-2.5 text-sm font-bold text-[#6d4235] transition hover:bg-[#fffaf8]"
                                    >

                                        <svg
                                            class="h-4 w-4"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                        >

                                            <circle
                                                cx="12"
                                                cy="12"
                                                r="9"
                                            />

                                            <path d="M12 10v6"/>

                                            <path d="M12 7h.01"/>

                                        </svg>

                                        تفاصيل

                                    </button>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="7"
                                    class="px-6 py-20 text-center"
                                >

                                    <div
                                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[#f5e9e4] text-2xl"
                                    >
                                        📅
                                    </div>

                                    <h3
                                        class="mt-5 text-lg font-bold text-[#4b2a22]"
                                    >
                                        لا توجد حجوزات
                                    </h3>

                                    <p
                                        class="mt-2 text-sm text-gray-400"
                                    >
                                        لا توجد حجوزات تطابق الفلتر الحالي.
                                    </p>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- Pagination --}}
            @if($this->bookings->hasPages())

                <div
                    class="border-t border-gray-100 px-6 py-5"
                >
                    {{ $this->bookings->links() }}
                </div>

            @endif

        </div>

    </div>


    {{-- ============================================================= --}}
    {{-- Details Modal --}}
    {{-- ============================================================= --}}

    @if($selectedBooking)

        @php

            $selectedDate = Carbon::parse(
                $selectedBooking['date']
            );

        @endphp


        <div
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
            wire:click.self="closeDetails"
        >

            <div
                class="w-full max-w-lg overflow-hidden rounded-[2rem] bg-white shadow-2xl"
                wire:click.stop
            >

                {{-- Header --}}
                <div
                    class="bg-gradient-to-br from-[#5b3025] to-[#8c5747] px-6 py-6 text-white"
                >

                    <div class="flex items-center justify-between">

                        <div>

                            <p
                                class="text-xs font-semibold text-white/60"
                            >
                                تفاصيل الحجز
                            </p>

                            <h2
                                class="mt-2 text-2xl font-bold"
                            >
                                الحجز #{{ $selectedBooking['id'] }}
                            </h2>

                        </div>


                        <button
                            type="button"
                            wire:click="closeDetails"
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-xl transition hover:bg-white/20"
                        >
                            ×
                        </button>

                    </div>

                </div>


                {{-- Body --}}
                <div class="space-y-4 p-6">

                    {{-- Date --}}
                    <div
                        class="flex items-center gap-4 rounded-2xl bg-[#fcfaf9] p-4"
                    >

                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#f5e9e4]"
                        >
                            📅
                        </div>

                        <div>

                            <p class="text-xs text-gray-400">
                                التاريخ
                            </p>

                            <p
                                class="mt-1 font-bold text-[#4b2a22]"
                            >
                                {{ $selectedDate->translatedFormat('l، d F Y') }}
                            </p>

                        </div>

                    </div>


                    {{-- Time --}}
                    <div
                        class="flex items-center gap-4 rounded-2xl bg-[#fcfaf9] p-4"
                    >

                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#f5e9e4]"
                        >
                            🕐
                        </div>

                        <div>

                            <p class="text-xs text-gray-400">
                                الوقت
                            </p>

                            <p
                                class="mt-1 font-bold text-[#4b2a22]"
                            >
                                {{ Carbon::parse($selectedBooking['time'])->format('h:i A') }}
                            </p>

                        </div>

                    </div>


                    {{-- Employee --}}
                    <div
                        class="flex items-center gap-4 rounded-2xl bg-[#fcfaf9] p-4"
                    >

                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#f5e9e4]"
                        >
                            👤
                        </div>

                        <div>

                            <p class="text-xs text-gray-400">
                                الحلاق
                            </p>

                            <p
                                class="mt-1 font-bold text-[#4b2a22]"
                            >
                                {{ $selectedBooking['employee_name'] }}
                            </p>

                        </div>

                    </div>


                    {{-- Services --}}
   <div class="rounded-2xl bg-[#fcfaf9] p-4">

    <p class="text-xs text-gray-400">
        الخدمات
    </p>

    <div class="mt-3 flex flex-wrap gap-2">
@forelse($selectedBooking['service_names'] ?? [] as $serviceName)

    <span
        class="rounded-full bg-[#f5e9e4] px-3 py-1.5 text-xs font-bold text-[#7b4537]"
    >
        {{ $serviceName }}
    </span>

@empty

    <span class="text-sm text-gray-400">
        غير محددة
    </span>

@endforelse

    </div>

</div>


                    {{-- Turn + Status --}}
                    <div class="grid grid-cols-2 gap-4">

                        {{-- Turn --}}
                        <div
                            class="rounded-2xl bg-[#fcfaf9] p-4"
                        >

                            <p class="text-xs text-gray-400">
                                رقم الدور
                            </p>

                            <p
                                class="mt-2 text-xl font-bold text-[#4b2a22]"
                            >

                                @if($selectedBooking['turn'] > 0)

                                    #{{ $selectedBooking['turn'] }}

                                @else

                                    <span class="text-green-600">
                                        ✓
                                    </span>

                                @endif

                            </p>

                        </div>


                        {{-- Status --}}
                        <div
                            class="rounded-2xl bg-[#fcfaf9] p-4"
                        >

                            <p class="text-xs text-gray-400">
                                الحالة
                            </p>

                            <div class="mt-2">

                                @if($selectedBooking['status'] === 'pending')

                                    <span
                                        class="inline-flex rounded-full bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700"
                               >   
                                        قيد الانتظار
                                    </span>

                                @elseif($selectedBooking['status'] === 'completed')

                                    <span
                                        class="inline-flex rounded-full bg-green-50 px-3 py-1.5 text-xs font-bold text-green-700"
                                    >
                                        مكتمل
                                    </span>

                                @elseif($selectedBooking['status'] === 'cancelled')

                                    <span
                                        class="inline-flex rounded-full bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700"
                                    >
                                        ملغي
                                    </span>

                                @else

                                    <span
                                        class="inline-flex rounded-full bg-gray-100 px-3 py-1.5 text-xs font-bold text-gray-600"
                                    >
                                        {{ $selectedBooking['status'] }}
                                    </span>

                                @endif

                            </div>

                        </div>

                    </div>

                </div>


                {{-- Footer --}}
                <div
                    class="border-t border-gray-100 bg-[#fcfaf9] p-5"
                >

                    <button
                        type="button"
                        wire:click="closeDetails"
                        class="w-full rounded-2xl bg-[#5b3025] px-5 py-3.5 text-sm font-bold text-white transition hover:bg-[#713c30]"
                    >
                        إغلاق
                    </button>

                </div>

            </div>

        </div>

    @endif

</div>