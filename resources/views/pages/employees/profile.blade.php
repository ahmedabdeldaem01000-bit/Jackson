<?php

use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('الملف الشخصي')] class extends Component
{
    public $employee;

    public function mount(): void
    {
        $this->employee = Auth::guard('employee')->user();
    }

    public function getTotalBookingsProperty()
    {
        return Booking::where('employee_id', $this->employee->id)->count();
    }

    public function getCompletedBookingsProperty()
    {
        return Booking::where('employee_id', $this->employee->id)
            ->where('status', 'completed')
            ->count();
    }

    public function getPendingBookingsProperty()
    {
        return Booking::where('employee_id', $this->employee->id)
            ->where('status', 'pending')
            ->count();
    }
};
?>
<div>

    <div class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">

                <div>
                    <h1 class="m-0 font-weight-bold">
                        الملف الشخصي
                    </h1>

                    <small class="text-muted">
                        معلومات حسابك وإحصائيات الحجوزات
                    </small>
                </div>

                <a
                    href="{{ route('employee.profile.show') }}"
                    class="btn btn-outline-secondary"
                >
                    <i class="fas fa-arrow-right ml-1"></i>
                    الرئيسية
                </a>

            </div>
        </div>
    </div>


    <section class="content">

        <div class="container-fluid">

            <div class="row">

                {{-- Profile --}}
                <div class="col-lg-4">

                    <div class="card card-widget widget-user shadow-sm">

                        <div
                            class="widget-user-header text-white"
                            style="
                                background: linear-gradient(
                                    135deg,
                                    #212529,
                                    #495057
                                );
                            "
                        >

                            <h3 class="widget-user-username">
                                {{ $employee->name }}
                            </h3>

                            <h5 class="widget-user-desc">
                                @if($employee->hasRole('admin'))
                                    مدير النظام
                                @elseif($employee->hasRole('barber'))
                                    حلاق
                                @else
                                    موظف
                                @endif
                            </h5>

                        </div>


                        <div class="widget-user-image">

                            <img
                                class="img-circle elevation-3"
                                src="{{ $employee->image
                                    ? asset('storage/' . $employee->image)
                                    : asset('dist/img/user2-160x160.jpg') }}"
                                alt="{{ $employee->name }}"
                                style="
                                    width: 100px;
                                    height: 100px;
                                    object-fit: cover;
                                "
                            >

                        </div>


                        <div class="card-footer pt-5">

                            <div class="text-center">

                                @if($employee->hasRole('admin'))

                                    <span class="badge badge-danger px-3 py-2">
                                        Admin
                                    </span>

                                @elseif($employee->hasRole('barber'))

                                    <span class="badge badge-primary px-3 py-2">
                                        Barber
                                    </span>

                                @else

                                    <span class="badge badge-success px-3 py-2">
                                        Employee
                                    </span>

                                @endif

                            </div>

                        </div>

                    </div>


                    {{-- Account Status --}}
                    <div class="card shadow-sm">

                        <div class="card-header">

                            <h3 class="card-title">
                                <i class="fas fa-shield-alt ml-1"></i>
                                حالة الحساب
                            </h3>

                        </div>

                        <div class="card-body">

                            <div class="d-flex justify-content-between mb-3">

                                <span>
                                    حالة الحساب
                                </span>

                                <span class="badge badge-success">
                                    نشط
                                </span>

                            </div>

                            <div class="d-flex justify-content-between">

                                <span>
                                    نوع الحساب
                                </span>

                                <strong>
                                    @if($employee->hasRole('admin'))
                                        مدير
                                    @elseif($employee->hasRole('barber'))
                                        حلاق
                                    @else
                                        موظف
                                    @endif
                                </strong>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- Information --}}
                <div class="col-lg-8">

                    <div class="card shadow-sm">

                        <div class="card-header">

                            <h3 class="card-title">
                                <i class="fas fa-user ml-1"></i>
                                بيانات الموظف
                            </h3>

                        </div>

                        <div class="card-body">

                            <div class="row">

                                {{-- Name --}}
                                <div class="col-md-6 mb-4">

                                    <div class="info-box bg-light">

                                        <span class="info-box-icon bg-primary">
                                            <i class="fas fa-user"></i>
                                        </span>

                                        <div class="info-box-content">

                                            <span class="info-box-text">
                                                الاسم
                                            </span>

                                            <span class="info-box-number">
                                                {{ $employee->name }}
                                            </span>

                                        </div>

                                    </div>

                                </div>


                                {{-- Email --}}
                                <div class="col-md-6 mb-4">

                                    <div class="info-box bg-light">

                                        <span class="info-box-icon bg-info">
                                            <i class="fas fa-envelope"></i>
                                        </span>

                                        <div class="info-box-content">

                                            <span class="info-box-text">
                                                البريد الإلكتروني
                                            </span>

                                            <span class="info-box-number"
                                                  style="font-size: 15px;">
                                                {{ $employee->email }}
                                            </span>

                                        </div>

                                    </div>

                                </div>


                                {{-- Phone --}}
                                <div class="col-md-6 mb-4">

                                    <div class="info-box bg-light">

                                        <span class="info-box-icon bg-success">
                                            <i class="fas fa-phone"></i>
                                        </span>

                                        <div class="info-box-content">

                                            <span class="info-box-text">
                                                رقم الهاتف
                                            </span>

                                            <span class="info-box-number">
                                                {{ $employee->phone ?? 'غير مضاف' }}
                                            </span>

                                        </div>

                                    </div>

                                </div>


                                {{-- Created At --}}
                                <div class="col-md-6 mb-4">

                                    <div class="info-box bg-light">

                                        <span class="info-box-icon bg-warning">
                                            <i class="fas fa-calendar"></i>
                                        </span>

                                        <div class="info-box-content">

                                            <span class="info-box-text">
                                                تاريخ الانضمام
                                            </span>

                                            <span class="info-box-number">
                                                {{ $employee->created_at?->format('Y-m-d') }}
                                            </span>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- Statistics --}}
                    <div class="card shadow-sm">

                        <div class="card-header">

                            <h3 class="card-title">
                                <i class="fas fa-chart-line ml-1"></i>
                                إحصائيات الحجوزات
                            </h3>

                        </div>

                        <div class="card-body">

                            <div class="row">

                                <div class="col-md-4">

                                    <div class="small-box bg-primary">

                                        <div class="inner">

                                            <h3>
                                                {{ $this->totalBookings }}
                                            </h3>

                                            <p>
                                                إجمالي الحجوزات
                                            </p>

                                        </div>

                                        <div class="icon">
                                            <i class="fas fa-calendar-check"></i>
                                        </div>

                                    </div>

                                </div>


                                <div class="col-md-4">

                                    <div class="small-box bg-success">

                                        <div class="inner">

                                            <h3>
                                                {{ $this->completedBookings }}
                                            </h3>

                                            <p>
                                                الحجوزات المكتملة
                                            </p>

                                        </div>

                                        <div class="icon">
                                            <i class="fas fa-check-circle"></i>
                                        </div>

                                    </div>

                                </div>


                                <div class="col-md-4">

                                    <div class="small-box bg-warning">

                                        <div class="inner">

                                            <h3>
                                                {{ $this->pendingBookings }}
                                            </h3>

                                            <p>
                                                الحجوزات المعلقة
                                            </p>

                                        </div>

                                        <div class="icon">
                                            <i class="fas fa-clock"></i>
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>

</div>