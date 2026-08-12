<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <a href="index3.html" class="brand-link">
    <img src="{{ asset('dist/img/AdminLTELogo.png') }}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
      style="opacity: .8">
    <span class="brand-text font-weight-light">Jackson Dashboard</span>
  </a>
  <form
    action="{{ route('employee.logout') }}"
    method="POST"
    class="inline"
>
    @csrf

    <button type="submit" class="text-white">
        تسجيل الخروج
    </button>
</form>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar user panel (optional) -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <img src="{{ asset('dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image">
      </div>
      <div class="info">
        <a href="#" class="d-block">Jackson</a>
      </div>
    </div>

    <!-- SidebarSearch Form -->
    <div class="form-inline">
      <div class="input-group" data-widget="sidebar-search">
        <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
        <div class="input-group-append">
          <button class="btn btn-sidebar">
            <i class="fas fa-search fa-fw"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
        <!-- parent -->
        <li class="nav-item menu-open">
          <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>
              الصفحه الرئيسه

            </p>
          </a>

        </li>


       <!-- parent -->
        <li class="nav-item {{ request()->routeIs('admin.employees.*') ? 'menu-open' : '' }}">
        <a href="{{ route('admin.employees.index') }}"     class="nav-link {{ request()->routeIs('admin.employees.index') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>
              الموظفين
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <!-- child -->
          <ul class="nav nav-treeview">
            <!-- sup child -->
            <li class="nav-item">
              <a href="{{ route('admin.employees.create') }}"
                class="nav-link {{ request()->routeIs('admin.employees.create') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>انشاء موظف جديد</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.employees.index') }}"
                class="nav-link {{ request()->routeIs('admin.employees.index') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>جميع الموظفين</p>
              </a>
            </li>

          </ul>
        </li>
       <!-- parent -->
        <li class="nav-item {{ request()->routeIs('admin.users.*') ? 'menu-open' : '' }}">
        <a href="{{ route('admin.users.index') }}"     class="nav-link {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>
              العملاء
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <!-- child -->
          <ul class="nav nav-treeview">
            <!-- sup child -->
            <li class="nav-item">
              <a href="{{ route('admin.users.create') }}"
                class="nav-link {{ request()->routeIs('admin.users.create') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>انشاء عميل جديد</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.users.index') }}"
                class="nav-link {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>جميع العملاء</p>
              </a>
            </li>

          </ul>
        </li>

 
   <!-- parent -->
        <li class="nav-item {{ request()->routeIs('admin.bookings.*') ? 'menu-open' : '' }}">
        <a href="{{ route('admin.bookings.index') }}"     class="nav-link {{ request()->routeIs('admin.bookings.index') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>
              الحجوزات
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <!-- child -->
          <ul class="nav nav-treeview">
            <!-- sup child -->
            <li class="nav-item">
              <a href="{{ route('admin.bookings.create') }}"
                class="nav-link {{ request()->routeIs('admin.bookings.create') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>انشاء حجوزات جديد</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.bookings.index') }}"
                class="nav-link {{ request()->routeIs('admin.bookings.index') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>جميع الحجوزات</p>
              </a>
            </li>

          </ul>
        </li>
   <!-- parent -->
        <li class="nav-item {{ request()->routeIs('admin.services.*') ? 'menu-open' : '' }}">
        <a href="{{ route('admin.services.index') }}"     class="nav-link {{ request()->routeIs('admin.services.index') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>
               الخدمات
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <!-- child -->
          <ul class="nav nav-treeview">
            <!-- sup child -->
            <li class="nav-item">
              <a href="{{ route('admin.services.create') }}"
                class="nav-link {{ request()->routeIs('admin.services.create') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>انشاء خدمه جديد</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.services.index') }}"
                class="nav-link {{ request()->routeIs('admin.services.index') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>جميع الحجوزات</p>
              </a>
            </li>

          </ul>
        </li>







      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>