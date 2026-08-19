<?php

use App\Livewire\Forms\BookingForm;
use App\Models\Booking;
use App\Models\Employee;
use App\Models\SubService;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('حجز موعد')] class extends Component
{
    public BookingForm $form;

    /*
    |--------------------------------------------------------------------------
    | Data
    |--------------------------------------------------------------------------
    */

    public $employees = [];

    public $services = [];

    public array $availableSlots = [];


    /*
    |--------------------------------------------------------------------------
    | Booking State
    |--------------------------------------------------------------------------
    */

    public bool $timeAvailable = false;

    public ?string $conflictMessage = null;


    /*
    |--------------------------------------------------------------------------
    | Modals
    |--------------------------------------------------------------------------
    */

    public bool $showTimeModal = false;

    public bool $showServicesModal = false;


    /*
    |--------------------------------------------------------------------------
    | Working Hours
    |--------------------------------------------------------------------------
    */

    public string $openingTime = '09:00';

    public string $closingTime = '21:00';


    /*
    |--------------------------------------------------------------------------
    | Mount
    |--------------------------------------------------------------------------
    */

    public function mount(): void
    {
        abort_unless(
            Auth::guard('customer')->check(),
            403
        );


        /*
        |--------------------------------------------------------------------------
        | Current Customer
        |--------------------------------------------------------------------------
        */

        $this->form->user_id = Auth::guard('customer')->id();


        /*
        |--------------------------------------------------------------------------
        | Today
        |--------------------------------------------------------------------------
        */

        $this->form->date = today()->toDateString();


        /*
        |--------------------------------------------------------------------------
        | Employees
        |--------------------------------------------------------------------------
        */

        $this->employees = Employee::query()
            ->select([
                'id',
                'name',
            ])
            ->orderBy('name')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Services
        |--------------------------------------------------------------------------
        */

        $this->services = SubService::query()
            ->select([
                'id',
                'name',
            ])
            ->orderBy('name')
            ->get();
    }


    /*
    |--------------------------------------------------------------------------
    | Selected Services
    |--------------------------------------------------------------------------
    */

    public function getSelectedServicesProperty(): array
    {
        $selectedIds = array_map(
            'intval',
            $this->form->service_ids
        );


        return collect($this->services)
            ->filter(
                fn ($service) =>
                    in_array(
                        (int) $service['id'],
                        $selectedIds,
                        true
                    )
            )
            ->values()
            ->toArray();
    }


    /*
    |--------------------------------------------------------------------------
    | Services Modal
    |--------------------------------------------------------------------------
    */

    public function openServicesModal(): void
    {
        $this->showServicesModal = true;
    }


    public function closeServicesModal(): void
    {
        $this->showServicesModal = false;
    }


    public function toggleService(int $serviceId): void
    {
        $serviceId = (int) $serviceId;


        if (
            in_array(
                $serviceId,
                array_map('intval', $this->form->service_ids),
                true
            )
        ) {
            $this->form->service_ids = array_values(
                array_diff(
                    array_map(
                        'intval',
                        $this->form->service_ids
                    ),
                    [$serviceId]
                )
            );

            return;
        }


        $this->form->service_ids[] = $serviceId;


        $this->form->service_ids = array_values(
            array_unique(
                array_map(
                    'intval',
                    $this->form->service_ids
                )
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Employee Changed
    |--------------------------------------------------------------------------
    */

    public function updatedFormEmployeeId(): void
    {
        $this->form->time = '';

        $this->timeAvailable = false;

        $this->conflictMessage = null;

        $this->resetErrorBag('form.time');

        $this->generateAvailableSlots();
    }


    /*
    |--------------------------------------------------------------------------
    | Time Modal
    |--------------------------------------------------------------------------
    */

    public function openTimeModal(): void
    {
        if (!$this->form->employee_id) {

            $this->addError(
                'form.employee_id',
                'اختار الحلاق الأول.'
            );

            return;
        }


        $this->generateAvailableSlots();

        $this->showTimeModal = true;
    }


    public function closeTimeModal(): void
    {
        $this->showTimeModal = false;
    }


    public function chooseTime(string $time): void
    {
        $isAvailable = collect(
            $this->availableSlots
        )->contains(
            fn ($slot) =>
                $slot['value'] === $time
        );


        if (!$isAvailable) {
            return;
        }


        $this->form->time = $time;


        $this->validateSelectedTime();


        if ($this->timeAvailable) {
            $this->showTimeModal = false;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Hour Slots
    |--------------------------------------------------------------------------
    |
    | العرض بالساعة فقط:
    | 09:00
    | 10:00
    | 11:00
    | ...
    |
    | ده لا يغيّر Business Rules الخاصة بالحجز.
    |
    */

    public function generateAvailableSlots(): void
    {
        $this->availableSlots = [];


        if (!$this->form->employee_id) {
            return;
        }


        $start = Carbon::createFromFormat(
            'H:i',
            $this->openingTime
        )->startOfHour();


        $end = Carbon::createFromFormat(
            'H:i',
            $this->closingTime
        )->startOfHour();


        while ($start->lt($end)) {

            $slot = $start->copy();


            /*
            |--------------------------------------------------------------------------
            | Don't show past hours
            |--------------------------------------------------------------------------
            */

            if (
                $this->form->date === today()->toDateString()
                &&
                $slot->isPast()
            ) {
                $start->addHour();

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Show hour
            |--------------------------------------------------------------------------
            */

            $this->availableSlots[] = [
                'value' => $slot->format('H:i'),

                'label' => $slot->format('g:i'),
            ];


            $start->addHour();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Selected Time
    |--------------------------------------------------------------------------
    */

    protected function validateSelectedTime(): void
    {
        $this->timeAvailable = false;

        $this->conflictMessage = null;

        $this->resetErrorBag('form.time');


        if (
            !$this->form->employee_id ||
            !$this->form->user_id ||
            !$this->form->time
        ) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Existing DB Check
        |--------------------------------------------------------------------------
        |
        | هنا بنفحص التعارض الحقيقي.
        | لكن لا نعرض الحجوزات نفسها للمستخدم.
        |
        */

        $bookings = Booking::query()
            ->where(
                'employee_id',
                $this->form->employee_id
            )
            ->whereDate(
                'date',
                today()
            )
            ->where(
                'status',
                'pending'
            )
            ->get([
                'time',
            ]);


        $selectedTime = Carbon::createFromFormat(
            'H:i',
            $this->form->time
        );


        $conflict = $bookings->first(
            function ($booking) use ($selectedTime) {

                $bookedTime = Carbon::parse(
                    $booking->time
                );


                return abs(
                    $bookedTime->diffInMinutes(
                        $selectedTime,
                        false
                    )
                ) <= 20;
            }
        );


        if ($conflict) {

            $this->form->time = '';

            $this->generateAvailableSlots();


            $this->addError(
                'form.time',
                'هذا الموعد لم يعد متاحًا. اختر موعدًا آخر.'
            );


            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Booking Service
        |--------------------------------------------------------------------------
        */

        $service = app(
            BookingService::class
        );


        $error = $service->hasConflict(
            (int) $this->form->employee_id,

            today()->toDateString(),

            $this->form->time,

            (int) $this->form->user_id
        );


        /*
        |--------------------------------------------------------------------------
        | Warning only
        |--------------------------------------------------------------------------
        */

        if ($error) {
            $this->conflictMessage = $error;
        }


        $this->timeAvailable = true;
    }


    /*
    |--------------------------------------------------------------------------
    | Save
    |--------------------------------------------------------------------------
    */

    public function save(): void
    {
        $customer = Auth::guard('customer')->user();


        abort_unless(
            $customer,
            403
        );


        /*
        |--------------------------------------------------------------------------
        | Never trust user_id from Browser
        |--------------------------------------------------------------------------
        */

        $this->form->user_id = $customer->id;


        /*
        |--------------------------------------------------------------------------
        | Today only
        |--------------------------------------------------------------------------
        */

        $this->form->date = today()->toDateString();


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $this->validate([
            'form.employee_id' => [
                'required',
                'exists:employees,id',
            ],

            'form.service_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'form.service_ids.*' => [
                'integer',
                'exists:sub_services,id',
            ],

            'form.time' => [
                'required',
                'date_format:H:i',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Validate Time Again
        |--------------------------------------------------------------------------
        */

        $this->validateSelectedTime();


        if (!$this->timeAvailable) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Store
        |--------------------------------------------------------------------------
        */

        $booking = $this->form->store();


        if (!$booking) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        session()->flash(
            'success',
            'تم إنشاء الحجز بنجاح.'
        );


        /*
        |--------------------------------------------------------------------------
        | Reset
        |--------------------------------------------------------------------------
        */

        $this->form->employee_id = '';

        $this->form->service_ids = [];

        $this->form->time = '';

        $this->form->user_id = $customer->id;

        $this->form->date = today()->toDateString();


        $this->availableSlots = [];

        $this->timeAvailable = false;

        $this->conflictMessage = null;
    }
};
?>


<section
    id="booking"
    class="relative w-full overflow-hidden bg-[#faf8f6] py-24"
>

    <div class="mx-auto max-w-6xl px-6">


        {{-- ========================================================= --}}
        {{-- Header --}}
        {{-- ========================================================= --}}

        <div class="mx-auto max-w-2xl text-center">

            <span
                class="inline-flex rounded-full bg-[#f1e5df] px-4 py-2 text-[11px] font-bold tracking-[0.25em] text-[#a56a58]"
            >
                BOOK YOUR SESSION
            </span>


            <h2
                class="heading-font mt-5 text-4xl font-semibold text-[#5b3025] sm:text-5xl"
            >
                احجز جلستك المميزة
            </h2>


            <p
                class="mx-auto mt-5 max-w-xl text-base leading-8 text-gray-600"
            >
                اختر الحلاق والخدمات والموعد المناسب لك، وسيتم تسجيل
                الحجز مباشرة على حسابك.
            </p>

        </div>


        {{-- ========================================================= --}}
        {{-- Guest --}}
        {{-- ========================================================= --}}

        @guest('customer')

            <div
                class="mx-auto mt-14 max-w-2xl rounded-[2rem] border border-[#eaded8] bg-white p-10 text-center shadow-[0_20px_70px_rgba(91,48,37,0.08)]"
            >

                <div
                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[#f6ebe6] text-2xl"
                >
                    🔐
                </div>


                <h3
                    class="mt-6 text-2xl font-bold text-[#5b3025]"
                >
                    سجل دخولك أولاً
                </h3>


                <p
                    class="mx-auto mt-3 max-w-md leading-7 text-gray-500"
                >
                    لازم تكون مسجل دخول علشان تقدر تحجز موعد
                    وتتابع حجوزاتك.
                </p>


                <a
                    href="{{ route('login') }}"
                    class="mt-7 inline-flex rounded-xl bg-[#5b3025] px-8 py-3 font-semibold text-white transition hover:bg-[#734034]"
                >
                    تسجيل الدخول
                </a>

            </div>

        @else


            {{-- ========================================================= --}}
            {{-- Success --}}
            {{-- ========================================================= --}}

            @if(session()->has('success'))

                <div
                    class="mx-auto mt-10 max-w-5xl rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-green-800"
                >

                    <div class="flex items-center gap-3">

                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-green-100"
                        >
                            ✓
                        </div>


                        <div>

                            <p class="font-bold">
                                تم الحجز بنجاح
                            </p>

                            <p class="mt-1 text-sm text-green-700">
                                تم تسجيل الحجز على حسابك.
                            </p>

                        </div>

                    </div>

                </div>

            @endif


            {{-- ========================================================= --}}
            {{-- Main Card --}}
            {{-- ========================================================= --}}

            <div
                class="mx-auto mt-14 max-w-5xl overflow-hidden rounded-[2rem] border border-[#eaded8] bg-white shadow-[0_25px_80px_rgba(91,48,37,0.08)]"
            >


                {{-- Header --}}
                <div
                    class="border-b border-gray-100 px-6 py-7 md:px-10"
                >

                    <div
                        class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between"
                    >

                        <div>

                            <p
                                class="text-xs font-semibold uppercase tracking-[0.2em] text-[#c98a6a]"
                            >
                                Appointment
                            </p>


                            <h3
                                class="mt-2 text-2xl font-bold text-[#5b3025]"
                            >
                                تفاصيل الحجز
                            </h3>

                        </div>


                        <div
                            class="rounded-2xl bg-[#faf4f0] px-5 py-3 text-right"
                        >

                            <p
                                class="text-[11px] font-semibold text-gray-400"
                            >
                                تاريخ الحجز
                            </p>


                            <p
                                class="mt-1 font-bold text-[#5b3025]"
                            >
                                {{ today()->format('Y-m-d') }}
                            </p>

                        </div>

                    </div>

                </div>


                {{-- Form --}}
                <form
                    wire:submit="save"
                    class="p-6 md:p-10"
                >

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">


                        {{-- ================================================= --}}
                        {{-- Customer --}}
                        {{-- ================================================= --}}

                        <div class="md:col-span-2">

                            <label
                                class="mb-2 block text-sm font-bold text-gray-700"
                            >
                                بيانات العميل
                            </label>


                            <div
                                class="flex items-center gap-4 rounded-2xl border border-green-100 bg-green-50 px-5 py-4"
                            >

                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-green-100 text-lg"
                                >
                                    👤
                                </div>


                                <div>

                                    <p class="text-xs text-green-600">
                                        سيتم تسجيل الحجز باسم
                                    </p>


                                    <p
                                        class="mt-1 font-bold text-green-800"
                                    >
                                        {{ auth('customer')->user()->name }}
                                    </p>

                                </div>

                            </div>

                        </div>


                        {{-- ================================================= --}}
                        {{-- Employee --}}
                        {{-- ================================================= --}}

                        <div>

                            <label
                                for="employee"
                                class="mb-2 block text-sm font-bold text-gray-700"
                            >
                                اختر الحلاق
                            </label>


                            <div class="relative">

                                <select
                                    id="employee"
                                    wire:model.live="form.employee_id"
                                    class="appointment-input w-full appearance-none pr-10"
                                >

                                    <option value="">
                                        اختر الحلاق
                                    </option>


                                    @foreach($employees as $employee)

                                        <option
                                            value="{{ $employee->id }}"
                                        >
                                            {{ $employee->name }}
                                        </option>

                                    @endforeach

                                </select>


                                <div
                                    class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-gray-400"
                                >
                                    ▼
                                </div>

                            </div>


                            @error('form.employee_id')

                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- ================================================= --}}
                        {{-- Services --}}
                        {{-- ================================================= --}}

                        <div>

                            <label
                                class="mb-2 block text-sm font-bold text-gray-700"
                            >
                                الخدمات
                            </label>


                            <button
                                type="button"
                                wire:click="openServicesModal"
                                class="flex w-full items-center justify-between rounded-2xl border border-gray-200 bg-white px-5 py-4 text-right shadow-sm transition hover:border-[#c98a6a] hover:bg-[#fffaf7]"
                            >

                                <div>

                                    <p class="text-xs text-gray-400">
                                        الخدمات المختارة
                                    </p>


                                    @if(count($form->service_ids))

                                        <p class="mt-1 font-semibold text-gray-800">
                                            تم اختيار
                                            {{ count($form->service_ids) }}
                                            خدمة
                                        </p>

                                    @else

                                        <p class="mt-1 font-semibold text-gray-500">
                                            اضغط لاختيار الخدمات
                                        </p>

                                    @endif

                                </div>


                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#f7ebe6] text-[#5b3025]"
                                >
                                    ✂
                                </div>

                            </button>


                            @if(count($form->service_ids))

                                <div class="mt-3 flex flex-wrap gap-2">

                                    @foreach($this->selectedServices as $service)

                                        <span
                                            class="inline-flex items-center gap-2 rounded-full bg-[#f4e8e3] px-3 py-2 text-sm font-medium text-[#5b3025]"
                                        >

                                            {{ $service['name'] }}


                                            <button
                                                type="button"
                                                wire:click="toggleService({{ $service['id'] }})"
                                                class="text-[#a56a58] transition hover:text-red-500"
                                            >
                                                ×
                                            </button>

                                        </span>

                                    @endforeach

                                </div>

                            @endif


                            @error('form.service_ids')

                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror


                            @error('form.service_ids.*')

                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- ================================================= --}}
                        {{-- Date --}}
                        {{-- ================================================= --}}

                        <div>

                            <label
                                class="mb-2 block text-sm font-bold text-gray-700"
                            >
                                التاريخ
                            </label>


                            <div
                                class="appointment-input flex items-center justify-between bg-gray-50"
                            >

                                <span>
                                    {{ today()->format('Y-m-d') }}
                                </span>


                                <span class="text-xs text-gray-400">
                                    اليوم
                                </span>

                            </div>

                        </div>


                        {{-- ================================================= --}}
                        {{-- Time --}}
                        {{-- ================================================= --}}

                        <div>

                            <label
                                class="mb-2 block text-sm font-bold text-gray-700"
                            >
                                اختر الوقت
                            </label>


                            <button
                                type="button"
                                wire:click="openTimeModal"
                                class="group flex w-full items-center justify-between rounded-2xl border border-gray-200 bg-white px-5 py-4 text-right shadow-sm transition hover:border-[#c98a6a] hover:bg-[#fffaf7]"
                            >

                                <div class="flex items-center gap-4">

                                    <div
                                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#f7ebe6] text-[#5b3025]"
                                    >
                                        🕐
                                    </div>


                                    <div>

                                        <p class="text-xs text-gray-400">
                                            الموعد
                                        </p>


                                        @if($form->time)

                                            <p
                                                class="mt-1 font-bold text-[#5b3025]"
                                            >
                                                {{
                                                    Carbon::createFromFormat(
                                                        'H:i',
                                                        $form->time
                                                    )->format('g:i')
                                                }}
                                            </p>

                                        @else

                                            <p
                                                class="mt-1 font-semibold text-gray-500"
                                            >
                                                اختر الوقت المناسب
                                            </p>

                                        @endif

                                    </div>

                                </div>


                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-50 text-gray-400"
                                >
                                    ›
                                </div>

                            </button>


                            @error('form.time')

                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- ================================================= --}}
                        {{-- Conflict --}}
                        {{-- ================================================= --}}

                        @if($conflictMessage)

                            <div class="md:col-span-2">

                                <div
                                    class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4"
                                >

                                    <div class="flex items-start gap-3">

                                        <div
                                            class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-100"
                                        >
                                            !
                                        </div>


                                        <div>

                                            <p
                                                class="font-bold text-amber-800"
                                            >
                                                تنبيه
                                            </p>


                                            <p
                                                class="mt-1 text-sm leading-6 text-amber-700"
                                            >
                                                {{ $conflictMessage }}
                                            </p>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        @endif


                        {{-- ================================================= --}}
                        {{-- Summary --}}
                        {{-- ================================================= --}}

                        @if(
                            $form->employee_id &&
                            count($form->service_ids) &&
                            $form->time
                        )

                            @php

                                $selectedEmployee = $employees->firstWhere(
                                    'id',
                                    (int) $form->employee_id
                                );

                            @endphp


                            <div class="md:col-span-2">

                                <div
                                    class="rounded-2xl border border-[#eaded8] bg-[#faf8f6] p-5"
                                >

                                    <div class="flex items-center gap-2">

                                        <span class="text-[#c98a6a]">
                                            ✓
                                        </span>


                                        <h4
                                            class="font-bold text-[#5b3025]"
                                        >
                                            ملخص الحجز
                                        </h4>

                                    </div>


                                    <div
                                        class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3"
                                    >

                                        {{-- Employee --}}
                                        <div
                                            class="rounded-xl bg-white p-4"
                                        >

                                            <p class="text-xs text-gray-400">
                                                الحلاق
                                            </p>


                                            <p
                                                class="mt-1 font-bold text-gray-800"
                                            >
                                                {{ $selectedEmployee?->name ?? '---' }}
                                            </p>

                                        </div>


                                        {{-- Services --}}
                                        <div
                                            class="rounded-xl bg-white p-4"
                                        >

                                            <p class="text-xs text-gray-400">
                                                الخدمات
                                            </p>


                                            <div class="mt-2 flex flex-wrap gap-1">

                                                @foreach($this->selectedServices as $service)

                                                    <span
                                                        class="rounded-full bg-[#f4e8e3] px-2 py-1 text-xs font-medium text-[#5b3025]"
                                                    >
                                                        {{ $service['name'] }}
                                                    </span>

                                                @endforeach

                                            </div>

                                        </div>


                                        {{-- Time --}}
                                        <div
                                            class="rounded-xl bg-white p-4"
                                        >

                                            <p class="text-xs text-gray-400">
                                                الوقت
                                            </p>


                                            <p
                                                class="mt-1 font-bold text-[#5b3025]"
                                            >
                                                {{
                                                    Carbon::createFromFormat(
                                                        'H:i',
                                                        $form->time
                                                    )->format('g:i')
                                                }}
                                            </p>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        @endif


                        {{-- ================================================= --}}
                        {{-- Submit --}}
                        {{-- ================================================= --}}

                        <div class="md:col-span-2">

                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                @disabled(
                                    !(
                                        $form->employee_id &&
                                        count($form->service_ids) &&
                                        $form->time
                                    )
                                )
                                class="w-full rounded-2xl bg-[#5b3025] px-6 py-4 text-sm font-bold tracking-wide text-white transition hover:bg-[#734034] disabled:cursor-not-allowed disabled:bg-gray-300"
                            >

                                <span wire:loading.remove>
                                    تأكيد الحجز
                                </span>


                                <span wire:loading>
                                    جاري تأكيد الحجز...
                                </span>

                            </button>

                        </div>

                    </div>

                </form>

            </div>


            {{-- ========================================================= --}}
            {{-- Services Modal --}}
            {{-- ========================================================= --}}

            @if($showServicesModal)

                <div
                    class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
                    wire:click.self="closeServicesModal"
                >

                    <div
                        class="w-full max-w-2xl overflow-hidden rounded-[2rem] bg-white shadow-2xl"
                    >

                        {{-- Header --}}
                        <div
                            class="border-b border-gray-100 px-5 py-5 sm:px-7"
                        >

                            <div
                                class="flex items-center justify-between gap-4"
                            >

                                <div>

                                    <p
                                        class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#c98a6a]"
                                    >
                                        Services
                                    </p>


                                    <h3
                                        class="mt-1 text-xl font-bold text-gray-900 sm:text-2xl"
                                    >
                                        اختر الخدمات
                                    </h3>


                                    <p
                                        class="mt-1 text-sm text-gray-500"
                                    >
                                        يمكنك اختيار خدمة واحدة أو أكثر
                                    </p>

                                </div>


                                <button
                                    type="button"
                                    wire:click="closeServicesModal"
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-500 transition hover:bg-gray-200"
                                >
                                    ✕
                                </button>

                            </div>

                        </div>


                        {{-- Body --}}
                        <div
                            class="max-h-[65vh] overflow-y-auto px-5 py-5 sm:px-7"
                        >

                            @if(count($services))

                                <div
                                    class="grid grid-cols-1 gap-3 sm:grid-cols-2"
                                >

                                    @foreach($services as $service)

                                        @php

                                            $selected = in_array(
                                                (int) $service['id'],
                                                array_map(
                                                    'intval',
                                                    $form->service_ids
                                                ),
                                                true
                                            );

                                        @endphp


                                        <button
                                            type="button"
                                            wire:key="service-{{ $service['id'] }}"
                                            wire:click="toggleService({{ $service['id'] }})"
                                            class="rounded-2xl border p-4 text-right transition
                                            {{
                                                $selected
                                                    ? 'border-[#5b3025] bg-[#faf3ef] ring-2 ring-[#5b3025]/10'
                                                    : 'border-gray-200 bg-white hover:border-[#c98a6a] hover:bg-[#fffaf7]'
                                            }}"
                                        >

                                            <div
                                                class="flex items-center justify-between gap-4"
                                            >

                                                <div>

                                                    <p
                                                        class="text-xs text-gray-400"
                                                    >
                                                        خدمة
                                                    </p>


                                                    <p
                                                        class="mt-1 font-semibold text-gray-800"
                                                    >
                                                        {{ $service['name'] }}
                                                    </p>

                                                </div>


                                                <div
                                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full
                                                    {{
                                                        $selected
                                                            ? 'bg-[#5b3025] text-white'
                                                            : 'bg-gray-100 text-gray-400'
                                                    }}"
                                                >

                                                    @if($selected)
                                                        ✓
                                                    @else
                                                        +
                                                    @endif

                                                </div>

                                            </div>

                                        </button>

                                    @endforeach

                                </div>

                            @else

                                <div
                                    class="py-10 text-center text-gray-500"
                                >
                                    لا توجد خدمات متاحة حاليًا.
                                </div>

                            @endif

                        </div>


                        {{-- Footer --}}
                        <div
                            class="border-t border-gray-100 bg-gray-50 px-5 py-4 sm:px-7"
                        >

                            <div
                                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                            >

                                <div class="text-sm text-gray-500">

                                    تم اختيار:

                                    <span
                                        class="font-bold text-[#5b3025]"
                                    >
                                        {{ count($form->service_ids) }}
                                    </span>

                                    خدمة

                                </div>


                                <button
                                    type="button"
                                    wire:click="closeServicesModal"
                                    class="w-full rounded-xl bg-[#5b3025] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#734034] sm:w-auto"
                                >
                                    تأكيد الاختيار
                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            @endif


            {{-- ========================================================= --}}
            {{-- Time Modal --}}
            {{-- ========================================================= --}}

            @if($showTimeModal)

                <div
                    class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
                    wire:click.self="closeTimeModal"
                >

                    <div
                        class="w-full max-w-2xl overflow-hidden rounded-[2rem] bg-white shadow-2xl"
                    >

                        {{-- Header --}}
                        <div
                            class="border-b border-gray-100 px-5 py-5 sm:px-7"
                        >

                            <div
                                class="flex items-center justify-between gap-4"
                            >

                                <div>

                                    <p
                                        class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#c98a6a]"
                                    >
                                        Available Times
                                    </p>


                                    <h3
                                        class="mt-1 text-xl font-bold text-[#5b3025] sm:text-2xl"
                                    >
                                        اختر موعدك
                                    </h3>


                                    <p
                                        class="mt-1 text-sm text-gray-500"
                                    >
                                        اختر الوقت المناسب لك
                                    </p>

                                </div>


                                <button
                                    type="button"
                                    wire:click="closeTimeModal"
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-500 transition hover:bg-gray-200"
                                >
                                    ✕
                                </button>

                            </div>

                        </div>


                        {{-- Body --}}
                        <div
                            class="max-h-[70vh] overflow-y-auto px-5 py-5 sm:px-7 sm:py-7"
                        >

                            @if(count($availableSlots))

                                <div
                                    class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4"
                                >

                                    @foreach($availableSlots as $slot)

                                        <button
                                            type="button"
                                            wire:key="time-{{ $slot['value'] }}"
                                            wire:click="chooseTime('{{ $slot['value'] }}')"
                                            class="rounded-2xl border px-4 py-4 text-center transition
                                            {{
                                                $form->time === $slot['value']
                                                    ? 'border-[#5b3025] bg-[#5b3025] text-white shadow-lg'
                                                    : 'border-gray-200 bg-[#faf8f6] text-gray-700 hover:border-[#c98a6a] hover:bg-[#fffaf7]'
                                            }}"
                                        >

                                            <div
                                                class="text-base font-bold sm:text-lg"
                                            >
                                                {{ $slot['label'] }}
                                            </div>


                                            <div
                                                class="mt-1 text-[11px]
                                                {{
                                                    $form->time === $slot['value']
                                                        ? 'text-white/70'
                                                        : 'text-gray-400'
                                                }}"
                                            >
                                                متاح
                                            </div>

                                        </button>

                                    @endforeach

                                </div>

                            @else

                                <div class="py-10 text-center">

                                    <div
                                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[#f7ebe6] text-2xl"
                                    >
                                        🕐
                                    </div>


                                    <h4
                                        class="mt-5 text-lg font-bold text-[#5b3025]"
                                    >
                                        لا توجد مواعيد متاحة
                                    </h4>


                                    <p
                                        class="mx-auto mt-2 max-w-sm text-sm leading-6 text-gray-500"
                                    >
                                        لا يوجد وقت متاح لهذا الحلاق اليوم.
                                        جرّب حلاقًا آخر.
                                    </p>

                                </div>

                            @endif

                        </div>


                        {{-- Footer --}}
                        <div
                            class="border-t border-gray-100 bg-gray-50 px-5 py-4 sm:px-7"
                        >

                            <div
                                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                            >

                                <div class="text-sm text-gray-500">

                                    @if($form->time)

                                        الموعد المختار:

                                        <span
                                            class="font-bold text-[#5b3025]"
                                        >
                                            {{
                                                Carbon::createFromFormat(
                                                    'H:i',
                                                    $form->time
                                                )->format('g:i')
                                            }}
                                        </span>

                                    @else

                                        لم يتم اختيار موعد بعد

                                    @endif

                                </div>


                                <button
                                    type="button"
                                    wire:click="closeTimeModal"
                                    class="w-full rounded-xl border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-100 sm:w-auto"
                                >
                                    إغلاق
                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            @endif

        @endguest

    </div>

</section>