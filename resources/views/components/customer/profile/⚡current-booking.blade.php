<?php

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
    #[Layout('layouts.customer')]
    #[Title('الحجز الحالي')]
class extends Component
{
    public $customer;

    public ?Booking $booking = null;

    public bool $showCancelModal = false;
    public bool $showRescheduleModal = false;

    public string $newTime = '';

    public array $availableTimes = [];

    public function mount(): void
    {
        $this->customer = Auth::guard('customer')->user();

        abort_unless($this->customer, 403);

        $this->loadCurrentBooking();
    }

    public function loadCurrentBooking(): void
    {
        $this->booking = Booking::query()
            ->where('user_id', $this->customer->id)
            ->where('status', 'pending')
            ->whereDate('date', today())
            ->orderBy('time')
            ->first();
    }

    public function refreshBooking(): void
    {
        $this->loadCurrentBooking();
    }

    public function openCancelModal(): void
    {
        $this->showCancelModal = true;
    }

    public function closeCancelModal(): void
    {
        $this->showCancelModal = false;
    }

    public function cancelBooking(): void
    {
        if (!$this->booking) {
            return;
        }

        $this->booking->update([
            'status' => 'cancelled',
        ]);

        $this->booking = null;

        $this->showCancelModal = false;

        session()->flash(
            'success',
            'تم إلغاء الحجز بنجاح.'
        );
    }

    public function openRescheduleModal(): void
    {
        if (!$this->booking) {
            return;
        }

        $this->newTime = '';

        $this->generateAvailableTimes();

        $this->showRescheduleModal = true;
    }

    public function closeRescheduleModal(): void
    {
        $this->showRescheduleModal = false;
        $this->newTime = '';
        $this->resetErrorBag('newTime');
    }

    public function generateAvailableTimes(): void
    {
        $this->availableTimes = [];

        if (!$this->booking) {
            return;
        }

        $start = now()
            ->copy()
            ->addHour()
            ->startOfHour();

        $end = now()
            ->copy()
            ->setTime(21, 0);

        while ($start->lt($end)) {

            $time = $start->format('H:i');

            if ($this->isTimeAvailable($time)) {

                $this->availableTimes[] = [
                    'value' => $time,
                    'label' => $start->format('h:i A'),
                ];
            }

            $start->addHour();
        }
    }

    protected function isTimeAvailable(string $time): bool
    {
        if (!$this->booking) {
            return false;
        }

        $selectedTime = Carbon::createFromFormat(
            'H:i',
            $time
        );

        $bookings = Booking::query()
            ->where('employee_id', $this->booking->employee_id)
            ->whereDate('date', today())
            ->where('status', 'pending')
            ->whereKeyNot($this->booking->id)
            ->get(['time']);

        return !$bookings->contains(function ($booking) use ($selectedTime) {

            $bookedTime = Carbon::parse($booking->time);

            return abs(
                $bookedTime->diffInMinutes(
                    $selectedTime,
                    false
                )
            ) <= 20;
        });
    }

    public function rescheduleBooking(): void
    {
        if (!$this->booking || !$this->newTime) {
            return;
        }

        if (!$this->isTimeAvailable($this->newTime)) {

            $this->addError(
                'newTime',
                'الموعد ده لم يعد متاحًا.'
            );

            $this->generateAvailableTimes();

            return;
        }

        $this->booking->update([
            'time' => $this->newTime,
        ]);

        $this->booking->refresh();

        $this->showRescheduleModal = false;

        $this->newTime = '';

        session()->flash(
            'success',
            'تم تأجيل موعد الحجز بنجاح.'
        );
    }
};
?>

<div
    wire:poll.15s="refreshBooking"
    class="px-4 py-8 sm:px-6 lg:px-8"
>
    <div class="mx-auto max-w-5xl">

        <div class="mb-8">

            <span
                class="inline-flex rounded-full bg-[#f3e5de] px-4 py-2 text-xs font-bold text-[#a56a58]"
            >
                موعدك
            </span>

            <h1 class="mt-4 text-3xl font-bold text-[#4b2a22]">
                الحجز الحالي
            </h1>

            <p class="mt-2 text-sm text-gray-500">
                تابع الوقت المتبقي وتحكم في موعد حجزك.
            </p>

        </div>


        @if(session('success'))

            <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-semibold text-green-700">
                {{ session('success') }}
            </div>

        @endif


        @if($booking)

            @php
                $appointmentAt = Carbon::parse($booking->date)
                    ->setTimeFromTimeString($booking->time);

                $appointmentIso = $appointmentAt->toIso8601String();
            @endphp


            {{-- Timer --}}
            <section
                class="overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-[#5b3025] via-[#754638] to-[#a56a58] p-8 text-center text-white shadow-[0_30px_80px_rgba(91,48,37,0.18)] sm:p-12"
            >

                <p class="text-sm font-semibold text-white/60">
                    الوقت المتبقي على موعدك
                </p>

                <div
                    x-data="{
                        target: new Date(@js($appointmentIso)).getTime(),
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
                    class="mt-6"
                >

                    <div
                        dir="ltr"
                        class="font-mono text-5xl font-bold tracking-widest sm:text-7xl"
                    >

                        <span x-text="format().hours"></span>
                        :
                        <span x-text="format().minutes"></span>
                        :
                        <span x-text="format().seconds"></span>

                    </div>

                </div>


                <p class="mt-6 text-sm text-white/60">
                    {{ $appointmentAt->translatedFormat('l، d F Y') }}
                    -
                    {{ $appointmentAt->format('h:i A') }}
                </p>

            </section>


            {{-- Details --}}
            <section
                class="mt-6 rounded-[2rem] border border-[#eaded8] bg-white p-6 shadow-sm sm:p-8"
            >

                <div class="grid gap-4 sm:grid-cols-2">

                    <div class="rounded-2xl bg-[#fcfaf9] p-5">
                        <p class="text-xs text-gray-400">
                            الوقت
                        </p>

                        <p class="mt-2 text-xl font-bold text-[#4b2a22]">
                            {{ $appointmentAt->format('h:i A') }}
                        </p>
                    </div>


                    <div class="rounded-2xl bg-[#fcfaf9] p-5">
                        <p class="text-xs text-gray-400">
                            رقم الدور
                        </p>

                        <p class="mt-2 text-xl font-bold text-[#4b2a22]">
                            #{{ $booking->turn }}
                        </p>
                    </div>

                </div>


                <div class="mt-6 grid gap-4 sm:grid-cols-2">

                    <div class="rounded-2xl bg-[#fcfaf9] p-5">
                        <p class="text-xs text-gray-400">
                            الحلاق
                        </p>

                        <p class="mt-2 font-bold text-[#4b2a22]">
                            {{ $booking->employee?->name ?? 'غير محدد' }}
                        </p>
                    </div>


                    <div class="rounded-2xl bg-[#fcfaf9] p-5">
                        <p class="text-xs text-gray-400">
                            الحالة
                        </p>

                        <span class="mt-2 inline-flex rounded-full bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">
                            قيد الانتظار
                        </span>
                    </div>

                </div>


                {{-- Actions --}}
                <div class="mt-6 flex flex-col gap-3 sm:flex-row">

                    <button
                        type="button"
                        wire:click="openRescheduleModal"
                        class="flex-1 rounded-2xl border border-[#d8c2b8] bg-white px-5 py-4 text-sm font-bold text-[#6d4235] transition hover:bg-[#fffaf8]"
                    >
                        تأجيل الموعد
                    </button>


                    <button
                        type="button"
                        wire:click="openCancelModal"
                        class="flex-1 rounded-2xl bg-red-50 px-5 py-4 text-sm font-bold text-red-600 transition hover:bg-red-100"
                    >
                        إلغاء الحجز
                    </button>

                </div>

            </section>


            {{-- Cancel Modal --}}
            @if($showCancelModal)

                <div
                    class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
                    wire:click.self="closeCancelModal"
                >

                    <div
                        class="w-full max-w-md rounded-[2rem] bg-white p-6 shadow-2xl"
                        wire:click.stop
                    >

                        <h2 class="text-xl font-bold text-[#4b2a22]">
                            إلغاء الحجز
                        </h2>

                        <p class="mt-3 text-sm leading-7 text-gray-500">
                            هل أنت متأكد من إلغاء الحجز؟
                        </p>

                        <div class="mt-6 flex gap-3">

                            <button
                                type="button"
                                wire:click="closeCancelModal"
                                class="flex-1 rounded-xl border border-gray-200 px-4 py-3 text-sm font-bold text-gray-600"
                            >
                                رجوع
                            </button>

                            <button
                                type="button"
                                wire:click="cancelBooking"
                                class="flex-1 rounded-xl bg-red-600 px-4 py-3 text-sm font-bold text-white"
                            >
                                تأكيد الإلغاء
                            </button>

                        </div>

                    </div>

                </div>

            @endif


            {{-- Reschedule Modal --}}
            @if($showRescheduleModal)

                <div
                    class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
                    wire:click.self="closeRescheduleModal"
                >

                    <div
                        class="w-full max-w-lg overflow-hidden rounded-[2rem] bg-white shadow-2xl"
                        wire:click.stop
                    >

                        <div
                            class="bg-gradient-to-br from-[#5b3025] to-[#8c5747] px-6 py-6 text-white"
                        >

                            <div class="flex items-center justify-between">

                                <div>

                                    <p class="text-xs text-white/60">
                                        تأجيل الموعد
                                    </p>

                                    <h2 class="mt-2 text-xl font-bold">
                                        اختار الوقت الجديد
                                    </h2>

                                </div>

                                <button
                                    type="button"
                                    wire:click="closeRescheduleModal"
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-xl"
                                >
                                    ×
                                </button>

                            </div>

                        </div>


                        <div class="p-6">

                            <p class="text-sm leading-7 text-gray-500">
                                اختار وقت جديد لنفس اليوم ونفس الحلاق.
                            </p>


                            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">

                                @forelse($availableTimes as $slot)

                                    <button
                                        type="button"
                                        wire:click="$set('newTime', '{{ $slot['value'] }}')"
                                        class="rounded-2xl border px-4 py-4 text-sm font-bold transition
                                        {{
                                            $newTime === $slot['value']
                                                ? 'border-[#5b3025] bg-[#5b3025] text-white'
                                                : 'border-gray-200 bg-[#fcfaf9] text-[#5b3025] hover:border-[#a56a58]'
                                        }}"
                                    >
                                        {{ $slot['label'] }}
                                    </button>

                                @empty

                                    <div class="col-span-full rounded-2xl bg-[#fcfaf9] p-6 text-center text-sm text-gray-500">
                                        مفيش مواعيد متاحة حاليًا.
                                    </div>

                                @endforelse

                            </div>


                            @error('newTime')

                                <p class="mt-3 text-sm text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror


                            <div class="mt-6 flex gap-3">

                                <button
                                    type="button"
                                    wire:click="closeRescheduleModal"
                                    class="flex-1 rounded-xl border border-gray-200 px-4 py-3 text-sm font-bold text-gray-600"
                                >
                                    إلغاء
                                </button>


                                <button
                                    type="button"
                                    wire:click="rescheduleBooking"
                                    @disabled(!$newTime)
                                    class="flex-1 rounded-xl bg-[#5b3025] px-4 py-3 text-sm font-bold text-white disabled:cursor-not-allowed disabled:bg-gray-300"
                                >
                                    حفظ الموعد
                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            @endif


        @else

            <section
                class="rounded-[2rem] border border-dashed border-[#decfc8] bg-white px-6 py-16 text-center"
            >

                <div class="text-5xl">
                    📅
                </div>

                <h2 class="mt-5 text-2xl font-bold text-[#4b2a22]">
                    لا يوجد حجز حالي
                </h2>

                <p class="mt-2 text-sm text-gray-500">
                    عندما يكون لديك حجز قادم، سيظهر هنا.
                </p>

            </section>

        @endif

    </div>
</div>