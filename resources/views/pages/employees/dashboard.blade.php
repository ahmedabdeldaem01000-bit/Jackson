<?php

use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('لوحة الموظف')] class extends Component
{
    public int $perPage = 5;

    #[Computed]
    public function employee()
    {
        return Auth::guard('employee')->user();
    }

    #[Computed]
    public function todayBookingsQuery()
    {
        return Booking::query()
            ->where('employee_id', $this->employee->id)
            ->whereDate('date', today());
    }

    #[Computed]
    public function totalBookings()
    {
        return (clone $this->todayBookingsQuery)->count();
    }

    #[Computed]
    public function pendingBookings()
    {
        return (clone $this->todayBookingsQuery)
            ->where('status', 'pending')
            ->count();
    }

    #[Computed]
    public function completedBookings()
    {
        return (clone $this->todayBookingsQuery)
            ->where('status', 'completed')
            ->count();
    }

    #[Computed]
    public function cancelledBookings()
    {
        return (clone $this->todayBookingsQuery)
            ->where('status', 'cancelled')
            ->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Current Booking
    |--------------------------------------------------------------------------
    */

    #[Computed]
    public function currentBooking()
    {
        return Booking::query()
            ->with(['user', 'service'])
            ->where('employee_id', $this->employee->id)
            ->whereDate('date', today())
            ->where('status', 'pending')
            ->where('turn', '>', 0)
            ->orderBy('turn')
            ->orderBy('time')
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Next Booking
    |--------------------------------------------------------------------------
    */

    #[Computed]
    public function nextBooking()
    {
        $currentTurn = $this->currentBooking?->turn;

        return Booking::query()
            ->with(['user', 'service'])
            ->where('employee_id', $this->employee->id)
            ->whereDate('date', today())
            ->where('status', 'pending')
            ->where('turn', '>', 0)
            ->when(
                $currentTurn,
                fn ($query) => $query->where('turn', '>', $currentTurn)
            )
            ->orderBy('turn')
            ->orderBy('time')
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    */

    #[Computed]
    public function queue()
    {
        return Booking::query()
            ->with(['user', 'service'])
            ->where('employee_id', $this->employee->id)
            ->whereDate('date', today())
            ->where('status', 'pending')
            ->where('turn', '>', 0)
            ->orderBy('turn')
            ->orderBy('time')
            ->limit($this->perPage)
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Complete Booking
    |--------------------------------------------------------------------------
    */

    public function completeBooking(int $bookingId, BookingService $bookingService): void
    {
        $booking = Booking::query()
            ->where('employee_id', $this->employee->id)
            ->findOrFail($bookingId);

        if (!$bookingService->complete($booking)) {
            session()->flash(
                'error',
                'لا يمكن إنهاء الحجز قبل موعده.'
            );

            return;
        }

        unset(
            $this->currentBooking,
            $this->nextBooking,
            $this->queue,
            $this->totalBookings,
            $this->pendingBookings,
            $this->completedBookings,
            $this->cancelledBookings
        );

        session()->flash(
            'success',
            'تم إنهاء الحجز بنجاح.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Cancel Booking
    |--------------------------------------------------------------------------
    */

    public function cancelBooking(int $bookingId, BookingService $bookingService): void
    {
        $booking = Booking::query()
            ->where('employee_id', $this->employee->id)
            ->findOrFail($bookingId);

        $bookingService->cancel($booking);

        unset(
            $this->currentBooking,
            $this->nextBooking,
            $this->queue,
            $this->totalBookings,
            $this->pendingBookings,
            $this->completedBookings,
            $this->cancelledBookings
        );

        session()->flash(
            'success',
            'تم إلغاء الحجز.'
        );
    }
};
?>

<div>

    {{-- Header --}}
    <div class="content-header">
        <div class="container-fluid">

            <div class="d-flex justify-content-between align-items-center">

                <div>
                    <h1 class="m-0 font-weight-bold">
                        أهلاً، {{ $this->employee->name }} 👋
                    </h1>

                    <small class="text-muted">
                        ملخص حجوزاتك اليوم
                    </small>
                </div>

                <div>
                    <span class="badge badge-dark px-3 py-2">
                        {{ now()->format('Y-m-d') }}
                    </span>
                </div>

            </div>

        </div>
    </div>


    <section class="content">

        <div class="container-fluid">

            {{-- Flash Messages --}}
            @if (session()->has('success'))

                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle ml-1"></i>

                    {{ session('success') }}

                    <button
                        type="button"
                        class="close"
                        data-dismiss="alert"
                    >
                        <span>&times;</span>
                    </button>
                </div>

            @endif


            @if (session()->has('error'))

                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle ml-1"></i>

                    {{ session('error') }}

                    <button
                        type="button"
                        class="close"
                        data-dismiss="alert"
                    >
                        <span>&times;</span>
                    </button>
                </div>

            @endif


            {{-- Statistics --}}
            <div class="row">

                {{-- Total --}}
                <div class="col-lg-3 col-md-6">

                    <div class="small-box bg-primary">

                        <div class="inner">

                            <h3>
                                {{ $this->totalBookings }}
                            </h3>

                            <p>
                                حجوزات اليوم
                            </p>

                        </div>

                        <div class="icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>

                        <a
                            href="{{ route('employee.bookings.index') }}"
                            class="small-box-footer"
                        >
                            عرض الحجوزات
                            <i class="fas fa-arrow-circle-left mr-1"></i>
                        </a>

                    </div>

                </div>


                {{-- Pending --}}
                <div class="col-lg-3 col-md-6">

                    <div class="small-box bg-warning">

                        <div class="inner">

                            <h3>
                                {{ $this->pendingBookings }}
                            </h3>

                            <p>
                                في الانتظار
                            </p>

                        </div>

                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>

                    </div>

                </div>


                {{-- Completed --}}
                <div class="col-lg-3 col-md-6">

                    <div class="small-box bg-success">

                        <div class="inner">

                            <h3>
                                {{ $this->completedBookings }}
                            </h3>

                            <p>
                                مكتملة
                            </p>

                        </div>

                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>

                    </div>

                </div>


                {{-- Cancelled --}}
                <div class="col-lg-3 col-md-6">

                    <div class="small-box bg-danger">

                        <div class="inner">

                            <h3>
                                {{ $this->cancelledBookings }}
                            </h3>

                            <p>
                                ملغاة
                            </p>

                        </div>

                        <div class="icon">
                            <i class="fas fa-times-circle"></i>
                        </div>

                    </div>

                </div>

            </div>


            {{-- Current / Next --}}
            <div class="row">

                {{-- Current --}}
                <div class="col-lg-6">

                    <div class="card shadow-sm">

                        <div class="card-header">

                            <h3 class="card-title font-weight-bold">
                                <i class="fas fa-play-circle text-primary ml-1"></i>
                                الحجز الحالي
                            </h3>

                        </div>

                        <div class="card-body">

                            @if($this->currentBooking)

                                <div class="row">

                                    <div class="col-md-4 text-center">

                                        <div
                                            class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center"
                                            style="width: 90px; height: 90px;"
                                        >

                                            <div>

                                                <div
                                                    style="font-size: 28px;"
                                                    class="font-weight-bold"
                                                >
                                                    {{ $this->currentBooking->turn }}
                                                </div>

                                                <small>
                                                    الدور
                                                </small>

                                            </div>

                                        </div>

                                    </div>


                                    <div class="col-md-8">

                                        <h4 class="font-weight-bold mb-2">

                                            {{ $this->currentBooking->user?->name ?? 'عميل' }}

                                        </h4>

                                        <p class="mb-1">

                                            <i class="fas fa-cut text-muted ml-1"></i>

                                            {{ $this->currentBooking->service?->name ?? 'الخدمة' }}

                                        </p>

                                        <p class="mb-3">

                                            <i class="fas fa-clock text-muted ml-1"></i>

                                            {{ $this->currentBooking->time }}

                                        </p>


                                        <div>

                                            <button
                                                type="button"
                                                wire:click="completeBooking({{ $this->currentBooking->id }})"
                                                wire:loading.attr="disabled"
                                                class="btn btn-success"
                                            >

                                                <i class="fas fa-check ml-1"></i>
                                                إكمال الحجز

                                            </button>


                                            <button
                                                type="button"
                                                wire:click="cancelBooking({{ $this->currentBooking->id }})"
                                                wire:loading.attr="disabled"
                                                class="btn btn-outline-danger"
                                            >

                                                <i class="fas fa-times ml-1"></i>
                                                إلغاء

                                            </button>

                                        </div>

                                    </div>

                                </div>

                            @else

                                <div class="text-center py-5">

                                    <i
                                        class="fas fa-calendar-check text-muted"
                                        style="font-size: 45px;"
                                    ></i>

                                    <h5 class="mt-3 text-muted">
                                        لا يوجد حجز حالي
                                    </h5>

                                    <p class="text-muted mb-0">
                                        لا يوجد حجز منتظر لك الآن.
                                    </p>

                                </div>

                            @endif

                        </div>

                    </div>

                </div>


                {{-- Next --}}
                <div class="col-lg-6">

                    <div class="card shadow-sm">

                        <div class="card-header">

                            <h3 class="card-title font-weight-bold">
                                <i class="fas fa-forward text-success ml-1"></i>
                                الحجز القادم
                            </h3>

                        </div>

                        <div class="card-body">

                            @if($this->nextBooking)

                                <div class="d-flex align-items-center">

                                    <div
                                        class="rounded-circle bg-light d-flex align-items-center justify-content-center mr-3"
                                        style="width: 75px; height: 75px;"
                                    >

                                        <strong
                                            class="text-primary"
                                            style="font-size: 24px;"
                                        >
                                            {{ $this->nextBooking->turn }}
                                        </strong>

                                    </div>


                                    <div>

                                        <h5 class="font-weight-bold mb-1">

                                            {{ $this->nextBooking->user?->name ?? 'عميل' }}

                                        </h5>

                                        <p class="mb-1 text-muted">

                                            {{ $this->nextBooking->service?->name ?? 'الخدمة' }}

                                        </p>

                                        <span class="badge badge-light">

                                            <i class="fas fa-clock ml-1"></i>

                                            {{ $this->nextBooking->time }}

                                        </span>

                                    </div>

                                </div>

                            @else

                                <div class="text-center py-5">

                                    <i
                                        class="fas fa-hourglass-end text-muted"
                                        style="font-size: 45px;"
                                    ></i>

                                    <h5 class="mt-3 text-muted">
                                        لا يوجد حجز قادم
                                    </h5>

                                </div>

                            @endif

                        </div>

                    </div>

                </div>

            </div>


            {{-- Queue --}}
            <div class="card shadow-sm">

                <div class="card-header">

                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-list-ol ml-1"></i>
                        الطابور الحالي
                    </h3>

                    <div class="card-tools">

                        <a
                            href="{{ route('employee.bookings.index') }}"
                            class="btn btn-sm btn-primary"
                        >
                            كل الحجوزات
                            <i class="fas fa-arrow-left mr-1"></i>
                        </a>

                    </div>

                </div>


                <div class="card-body p-0">

                    @if($this->queue->isNotEmpty())

                        <div class="table-responsive">

                            <table class="table table-hover mb-0">

                                <thead class="thead-light">

                                    <tr>
                                        <th>#</th>
                                        <th>العميل</th>
                                        <th>الخدمة</th>
                                        <th>الوقت</th>
                                        <th>الحالة</th>
                                    </tr>

                                </thead>


                                <tbody>

                                    @foreach($this->queue as $booking)

                                        <tr>

                                            <td>
                                                <span class="badge badge-dark">
                                                    {{ $booking->turn }}
                                                </span>
                                            </td>

                                            <td>
                                                {{ $booking->user?->name ?? '---' }}
                                            </td>

                                            <td>
                                                {{ $booking->service?->name ?? '---' }}
                                            </td>

                                            <td>
                                                {{ $booking->time }}
                                            </td>

                                            <td>

                                                <span class="badge badge-warning">
                                                    في الانتظار
                                                </span>

                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    @else

                        <div class="text-center py-5">

                            <i
                                class="fas fa-inbox text-muted"
                                style="font-size: 50px;"
                            ></i>

                            <h5 class="mt-3">
                                لا توجد حجوزات في الطابور
                            </h5>

                            <p class="text-muted">
                                مفيش حجوزات منتظرة حاليًا.
                            </p>

                        </div>

                    @endif

                </div>

            </div>


            {{-- Quick Actions --}}
            <div class="row">

                <div class="col-md-4">

                    <a
                        href="{{ route('employee.bookings.index') }}"
                        class="btn btn-primary btn-block btn-lg"
                    >

                        <i class="fas fa-calendar-alt ml-1"></i>

                        حجوزاتي

                    </a>

                </div>


                <div class="col-md-4">

                    <a
                        href="{{ route('employee.bookings.create') }}"
                        class="btn btn-success btn-block btn-lg"
                    >

                        <i class="fas fa-plus-circle ml-1"></i>

                        إنشاء حجز

                    </a>

                </div>


                <div class="col-md-4">

                    <a
                        href="{{ route('employee.profile.show') }}"
                        class="btn btn-secondary btn-block btn-lg"
                    >

                        <i class="fas fa-user ml-1"></i>

                        الملف الشخصي

                    </a>

                </div>

            </div>

        </div>

    </section>

</div>