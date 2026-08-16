<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">

    {{-- ========================================================= --}}
    {{-- Brand --}}
    {{-- ========================================================= --}}

    @php
        $employee = auth('employee')->user();

        $isAdmin = $employee?->hasRole('admin');
        $isEmployee = $employee?->hasRole('employee');
        $isBarber = $employee?->hasRole('barber');
    @endphp

    <a
        href="{{ $isAdmin
            ? route('admin.dashboard')
            : route('employee.dashboard')
        }}"
        class="brand-link"
    >

        <img
            src="{{ asset('dist/img/AdminLTELogo.png') }}"
            alt="Logo"
            class="brand-image img-circle elevation-3"
            style="opacity: .8"
        >

        <span class="brand-text font-weight-light">
            Booking System
        </span>

    </a>


    {{-- ========================================================= --}}
    {{-- Sidebar --}}
    {{-- ========================================================= --}}

    <div class="sidebar">


        {{-- ========================================================= --}}
        {{-- User Panel --}}
        {{-- ========================================================= --}}

        <div class="user-panel mt-3 pb-3 mb-3 d-flex">

            <div class="image">

                <img
                    src="{{ $employee?->image
                        ? asset('storage/' . $employee->image)
                        : asset('dist/img/user2-160x160.jpg') }}"
                    class="img-circle elevation-2"
                    alt="{{ $employee?->name }}"
                    style="width: 35px; height: 35px; object-fit: cover;"
                >

            </div>


            <div class="info">

                <a href="#" class="d-block">

                    {{ $employee?->name ?? 'المستخدم' }}

                </a>


                <small class="text-muted">

                    @if($isAdmin)
                        مدير النظام
                    @elseif($isBarber)
                        حلاق
                    @else
                        موظف
                    @endif

                </small>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- Logout --}}
        {{-- ========================================================= --}}

        <div class="mb-3 px-2">

            <form
                action="{{ route('employee.logout') }}"
                method="POST"
            >

                @csrf

                <button
                    type="submit"
                    class="btn btn-danger btn-block"
                >

                    <i class="fas fa-sign-out-alt mr-2"></i>

                    تسجيل الخروج

                </button>

            </form>

        </div>


        {{-- ========================================================= --}}
        {{-- Search --}}
        {{-- ========================================================= --}}

        <div class="form-inline mb-3">

            <div
                class="input-group"
                data-widget="sidebar-search"
            >

                <input
                    class="form-control form-control-sidebar"
                    type="search"
                    placeholder="بحث"
                    aria-label="Search"
                >

                <div class="input-group-append">

                    <button class="btn btn-sidebar">

                        <i class="fas fa-search fa-fw"></i>

                    </button>

                </div>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- Sidebar Menu --}}
        {{-- ========================================================= --}}

        <nav class="mt-2">

            <ul
                class="nav nav-pills nav-sidebar flex-column"
                data-widget="treeview"
                role="menu"
                data-accordion="false"
            >


                {{-- ================================================= --}}
                {{-- ADMIN --}}
                {{-- ================================================= --}}

                @if($isAdmin)

                    {{-- Dashboard --}}
                    <li class="nav-item">

                        <a
                            href="{{ route('admin.dashboard') }}"
                            class="nav-link {{
                                request()->routeIs('admin.dashboard')
                                    ? 'active'
                                    : ''
                            }}"
                        >

                            <i class="nav-icon fas fa-tachometer-alt"></i>

                            <p>
                                الصفحة الرئيسية
                            </p>

                        </a>

                    </li>


                    {{-- Employees --}}
                    <li class="nav-item {{
                        request()->routeIs('admin.employees.*')
                            ? 'menu-open'
                            : ''
                    }}">

                        <a
                            href="{{ route('admin.employees.index') }}"
                            class="nav-link {{
                                request()->routeIs('admin.employees.*')
                                    ? 'active'
                                    : ''
                            }}"
                        >

                            <i class="nav-icon fas fa-users"></i>

                            <p>
                                الموظفين
                                <i class="right fas fa-angle-left"></i>
                            </p>

                        </a>


                        <ul class="nav nav-treeview">

                            <li class="nav-item">

                                <a
                                    href="{{ route('admin.employees.index') }}"
                                    class="nav-link {{
                                        request()->routeIs('admin.employees.index')
                                            ? 'active'
                                            : ''
                                    }}"
                                >

                                    <i class="far fa-circle nav-icon"></i>

                                    <p>
                                        جميع الموظفين
                                    </p>

                                </a>

                            </li>


                            <li class="nav-item">

                                <a
                                    href="{{ route('admin.employees.create') }}"
                                    class="nav-link {{
                                        request()->routeIs('admin.employees.create')
                                            ? 'active'
                                            : ''
                                    }}"
                                >

                                    <i class="far fa-circle nav-icon"></i>

                                    <p>
                                        إنشاء موظف
                                    </p>

                                </a>

                            </li>

                        </ul>

                    </li>


                    {{-- Customers --}}
                    <li class="nav-item {{
                        request()->routeIs('admin.users.*')
                            ? 'menu-open'
                            : ''
                    }}">

                        <a
                            href="{{ route('admin.users.index') }}"
                            class="nav-link {{
                                request()->routeIs('admin.users.*')
                                    ? 'active'
                                    : ''
                            }}"
                        >

                            <i class="nav-icon fas fa-user-friends"></i>

                            <p>
                                العملاء
                                <i class="right fas fa-angle-left"></i>
                            </p>

                        </a>


                        <ul class="nav nav-treeview">

                            <li class="nav-item">

                                <a
                                    href="{{ route('admin.users.index') }}"
                                    class="nav-link {{
                                        request()->routeIs('admin.users.index')
                                            ? 'active'
                                            : ''
                                    }}"
                                >

                                    <i class="far fa-circle nav-icon"></i>

                                    <p>
                                        جميع العملاء
                                    </p>

                                </a>

                            </li>


                            <li class="nav-item">

                                <a
                                    href="{{ route('admin.users.create') }}"
                                    class="nav-link {{
                                        request()->routeIs('admin.users.create')
                                            ? 'active'
                                            : ''
                                    }}"
                                >

                                    <i class="far fa-circle nav-icon"></i>

                                    <p>
                                        إنشاء عميل
                                    </p>

                                </a>

                            </li>

                        </ul>

                    </li>


                    {{-- Bookings --}}
                    <li class="nav-item {{
                        request()->routeIs('admin.bookings.*')
                            ? 'menu-open'
                            : ''
                    }}">

                        <a
                            href="{{ route('admin.bookings.index') }}"
                            class="nav-link {{
                                request()->routeIs('admin.bookings.*')
                                    ? 'active'
                                    : ''
                            }}"
                        >

                            <i class="nav-icon fas fa-calendar-check"></i>

                            <p>
                                الحجوزات
                                <i class="right fas fa-angle-left"></i>
                            </p>

                        </a>


                        <ul class="nav nav-treeview">

                            <li class="nav-item">

                                <a
                                    href="{{ route('admin.bookings.index') }}"
                                    class="nav-link {{
                                        request()->routeIs('admin.bookings.index')
                                            ? 'active'
                                            : ''
                                    }}"
                                >

                                    <i class="far fa-circle nav-icon"></i>

                                    <p>
                                        جميع الحجوزات
                                    </p>

                                </a>

                            </li>


                            <li class="nav-item">

                                <a
                                    href="{{ route('admin.bookings.create') }}"
                                    class="nav-link {{
                                        request()->routeIs('admin.bookings.create')
                                            ? 'active'
                                            : ''
                                    }}"
                                >

                                    <i class="far fa-circle nav-icon"></i>

                                    <p>
                                        إنشاء حجز
                                    </p>

                                </a>

                            </li>

                        </ul>

                    </li>


                    {{-- Services --}}
                    <li class="nav-item {{
                        request()->routeIs('admin.services.*')
                            ? 'menu-open'
                            : ''
                    }}">

                        <a
                            href="{{ route('admin.services.index') }}"
                            class="nav-link {{
                                request()->routeIs('admin.services.*')
                                    ? 'active'
                                    : ''
                            }}"
                        >

                            <i class="nav-icon fas fa-cut"></i>

                            <p>
                                الخدمات
                                <i class="right fas fa-angle-left"></i>
                            </p>

                        </a>


                        <ul class="nav nav-treeview">

                            <li class="nav-item">

                                <a
                                    href="{{ route('admin.services.index') }}"
                                    class="nav-link {{
                                        request()->routeIs('admin.services.index')
                                            ? 'active'
                                            : ''
                                    }}"
                                >

                                    <i class="far fa-circle nav-icon"></i>

                                    <p>
                                        جميع الخدمات
                                    </p>

                                </a>

                            </li>


                            <li class="nav-item">

                                <a
                                    href="{{ route('admin.services.create') }}"
                                    class="nav-link {{
                                        request()->routeIs('admin.services.create')
                                            ? 'active'
                                            : ''
                                    }}"
                                >

                                    <i class="far fa-circle nav-icon"></i>

                                    <p>
                                        إنشاء خدمة
                                    </p>

                                </a>

                            </li>

                        </ul>

                    </li>

                @endif


                {{-- ================================================= --}}
                {{-- EMPLOYEE / BARBER --}}
                {{-- ================================================= --}}

                @if($isEmployee || $isBarber)

                    {{-- Dashboard --}}
                    <li class="nav-item">

                        <a
                            href="{{ route('employee.dashboard') }}"
                            class="nav-link {{
                                request()->routeIs('employee.dashboard')
                                    ? 'active'
                                    : ''
                            }}"
                        >

                            <i class="nav-icon fas fa-tachometer-alt"></i>

                            <p>
                                الصفحة الرئيسية
                            </p>

                        </a>

                    </li>


                    {{-- My Bookings --}}
                    <li class="nav-item">

                        <a
                            href="{{ route('employee.bookings.index') }}"
                            class="nav-link {{
                                request()->routeIs('employee.bookings.index')
                                    ? 'active'
                                    : ''
                            }}"
                        >

                            <i class="nav-icon fas fa-calendar-check"></i>

                            <p>
                                حجوزاتي
                            </p>

                        </a>

                    </li>


                    {{-- Create Booking --}}
                    <li class="nav-item">

                        <a
                            href="{{ route('employee.bookings.create') }}"
                            class="nav-link {{
                                request()->routeIs('employee.bookings.create')
                                    ? 'active'
                                    : ''
                            }}"
                        >

                            <i class="nav-icon fas fa-plus-circle"></i>

                            <p>
                                إنشاء حجز
                            </p>

                        </a>

                    </li>


                    {{-- Profile --}}
                    <li class="nav-item">

                        <a
                            href="{{ route('employee.profile.show') }}"
                            class="nav-link {{
                                request()->routeIs('employee.profile.show')
                                    ? 'active'
                                    : ''
                            }}"
                        >

                            <i class="nav-icon fas fa-user"></i>

                            <p>
                                الملف الشخصي
                            </p>

                        </a>

                    </li>

                @endif


            </ul>

        </nav>

    </div>

</aside>