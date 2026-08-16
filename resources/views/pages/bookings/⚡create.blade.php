<?php

use Livewire\Component;
use App\Livewire\Forms\BookingForm;
use App\Models\User;
use App\Models\SubService;
use App\Models\Employee;
use App\Models\Booking;
use App\Services\BookingService;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
new #[Title('Create Bookings')] class extends Component {
    public BookingForm $form;

    public string $searchUser = '';
public bool $userSelected = false;
public bool $EmployeeSelected = false;
public bool $serviceSelected = false;
    public array $users = [];
    public string $searchService = '';

    public array $service = [];
    public string $searchEmployee = '';

    public array $employees = [];


    public array $bookedTimes = [];

    public bool $timeAvailable = false;

public function mount(): void
{
    if (Auth::guard('employee')->check()) {
        $employee = Auth::guard('employee')->user();

        if ($employee->hasAnyRole(['employee', 'barber'])) {
            $this->form->employee_id = $employee->id;

            $this->EmployeeSelected = true;
            $this->searchEmployee = $employee->name;

            $this->loadBookedTimes();
        }
    }
}

    public function updatedSearchUser()
    {
         $this->userSelected = false;
        if (strlen($this->searchUser) < 2) {
            $this->users = [];
            return;
        }

        $this->users = User::query()
            ->where('name', 'like', "%{$this->searchUser}%")
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function selectUser($id)
    {

        $user = User::findOrFail($id);
        
        $this->userSelected = true;
        $this->form->user_id = $user->id;
        $this->searchUser = $user->name;
        $this->users = [];

        if ($this->form->time) {
            $this->updatedFormTime();
        }
    }
 
    // -------------------------------------------------------------
// -------------------------------------------------------------




    public function updatedSearchService()
    {
   $this->serviceSelected = false;
        if (strlen($this->searchService) < 2) {
            $this->service = [];
            return;
        }

        $this->service = SubService::query()
            ->where('name', 'like', "%{$this->searchService}%")
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function selectService($id)
    {
        $service = SubService::findOrFail($id);
$this->serviceSelected=true;
        $this->form->service_id = $service->id;

        $this->searchService = $service->name;

        $this->service = [];
    }
    // -------------------------------------------------------------
// -------------------------------------------------------------




public function updatedSearchEmployee(): void
{
    $employee = Auth::guard('employee')->user();

    if ($employee?->hasAnyRole(['employee', 'barber'])) {
        return;
    }

    $this->EmployeeSelected = false;

    if (strlen($this->searchEmployee) < 2) {
        $this->employees = [];

        return;
    }

    $this->employees = Employee::query()
        ->where('name', 'like', "%{$this->searchEmployee}%")
        ->limit(10)
        ->get()
        ->toArray();
}

    public function loadBookedTimes()
    {
        $this->bookedTimes = Booking::query()
            ->where('employee_id', $this->form->employee_id)
            ->whereDate('date', today())
            ->where('status', 'pending')
            ->orderBy('time')
            ->get()
            ->map(function ($booking) {

                return [
                    'time' => $booking->time,
                    'formatted' => Carbon::parse($booking->time)
                        ->format('h:i A'),
                ];

            })
            ->toArray();
    }
public function selectEmployee($id): void
{
    $currentEmployee = Auth::guard('employee')->user();

    if ($currentEmployee?->hasAnyRole(['employee', 'barber'])) {
        return;
    }

    $employee = Employee::findOrFail($id);

    $this->EmployeeSelected = true;

    $this->form->employee_id = $employee->id;

    $this->searchEmployee = $employee->name;

    $this->employees = [];

    $this->loadBookedTimes();

    if ($this->form->time) {
        $this->updatedFormTime();
    }
}

    public function updatedFormEmployeeId()
    {
        if ($this->form->time) {
            $this->updatedFormTime();
        }
    }

    public function updatedFormUserId()
    {
        if ($this->form->time) {
            $this->updatedFormTime();
        }
    }
    public function updated($property)
    {
        if ($property === 'form.time') {
            $this->validateTime();
        }
    }

    protected function validateTime()
    {
        if (
            empty($this->form->employee_id) ||
            empty($this->form->user_id) ||
            empty($this->form->time)
        ) {
            $this->timeAvailable = false;
            return;
        }

        $service = app(BookingService::class);

        $this->resetErrorBag('form.time');

        $error = $service->hasConflict(
            $this->form->employee_id,
            now()->toDateString(),
            $this->form->time,
            $this->form->user_id
        );

        if ($error) {
            $this->addError('form.time', $error);
            $this->timeAvailable = false;
        } else {
            $this->timeAvailable = true;
        }
    }
    public function updatedFormTime()
    {
        if (
            empty($this->form->employee_id) ||
            empty($this->form->user_id) ||
            empty($this->form->time)
        ) {
            $this->timeAvailable = false;
            return;
        }

        $service = app(BookingService::class);

        $this->resetErrorBag('form.time');

        $error = $service->hasConflict(
            $this->form->employee_id,
            now()->toDateString(),
            $this->form->time,
            $this->form->user_id,
        );

        if ($error) {
            $this->addError('form.time', $error);
            $this->timeAvailable = false;
        } else {
            $this->timeAvailable = true;
        }
    }
  public function save()
{
    $employee = Auth::guard('employee')->user();

    if (!$employee) {
        abort(403);
    }

    // الموظف أو الحلاق لا يختار موظفًا آخر
    if ($employee->hasAnyRole(['employee', 'barber'])) {
        $this->form->employee_id = $employee->id;
    }

    $this->form->date = now()->toDateString();

    $this->form->store();

    session()->flash(
        'success',
        'تم إضافة الحجز بنجاح'
    );

    return $this->redirect(
        $employee->hasRole('admin')
            ? route('admin.bookings.index')
            : route('employee.bookings.index')
    );
}
};


?>


<div class="max-w-7xl mx-auto px-6 py-8">

    {{-- Header --}}
    <div class="mb-8">

        <h1 class="text-3xl font-bold text-gray-900">

            إنشاء حجز جديد

        </h1>

        <p class="mt-2 text-gray-500">

            اختر العميل والخدمة والموظف ثم انتقل لاختيار موعد الحجز.

        </p>

    </div>

    @if(session()->has('error'))

        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">

            <div class="flex items-center gap-3">

                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">

                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M12 3C7.03 3 3 7.03 3 12s4.03 9 9 9 9-4.03 9-9-4.03-9-9-9z" />

                </svg>

                <span class="font-medium text-red-700">

                    {{ session('error') }}

                </span>

            </div>

        </div>

    @endif

    <form wire:submit="save">

        <div class="rounded-2xl justify-center  border border-gray-200 bg-white shadow-sm">

            <div class="border-b border-gray-100 px-6 py-5">

                <div class="flex items-center gap-4">

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100">

                        👤

                    </div>

                    <div>

                        <h2 class="text-xl font-bold text-gray-800">

                            بيانات الحجز

                        </h2>

                        <p class="text-sm text-gray-500">

                            اختر بيانات الحجز الأساسية.

                        </p>

                    </div>

                </div>

            </div>

            <div class="p-6 justify-center">

                <div class="space-y-2 w-[50%] justify-center">

                    {{-- Personal Information --}}

                    <div class="bg-white shadow sm:rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
                                بيانات الحجز
                            </h3>
                            <div class="relative">

                                <label class="mb-2 flex items-center gap-2 text-sm font-semibold text-gray-700">

                                    👤 العميل

                                </label>

                                <input type="text" wire:model.live.debounce.300ms="searchUser"
                                    placeholder="ابحث عن العميل..." autocomplete="off" class="w-full rounded-xl border border-gray-300 bg-gray-50
px-4 py-3 shadow-sm transition
focus:border-indigo-500
focus:bg-white
focus:ring-2
focus:ring-indigo-500">

                                <input type="hidden" wire:model="form.user_id">

                                @if($searchUser && count($users))
                                                                    <div class="absolute z-50 mt-2 w-full overflow-hidden
                                    rounded-xl border border-gray-200 bg-white shadow-xl">

                                                                        @foreach($users as $user)

                                                                            <button type="button" wire:key="user-{{ $user['id'] }}"
                                                                                wire:click="selectUser({{ $user['id'] }})"
                                                                              class="flex w-full items-center justify-between
px-4 py-3 text-right
transition
hover:bg-indigo-50">
                                                                                {{ $user['name'] }}
                                                                            </button>

                                                                        @endforeach

                                                                    </div>
                              @elseif($searchUser && !$userSelected)
                                    <div class="mt-1 bg-black border rounded-lg shadow-lg">
                                        <p class="px-4 py-2 text-right text-gray-500">لا يوجد نتائج</p>
                                    </div>
                                @endif

                            </div>



                            @error('form.user_id')
                            <p
class="mt-2 flex items-center gap-2 text-sm text-red-600">

<span>⚠</span>

<span>

{{ $message }}

</span>

</p>
                            @enderror

                            <!-- ++++++++++++++++++++++++++++++++++++++ -->

                            <div class="relative">

                                <label class="mb-2 flex items-center gap-2 text-sm font-semibold text-gray-700">

                                    ✂️ الخدمة

                                </label>

                                <input type="text" wire:model.live.debounce.300ms="searchService"
                                    placeholder="ابحث عن خدمه..." autocomplete="off" class="w-full rounded-xl border border-gray-300 bg-gray-50
px-4 py-3 shadow-sm transition
focus:border-indigo-500
focus:bg-white
focus:ring-2
focus:ring-indigo-500">

                                <input type="hidden" wire:model="form.service_id">

                                @if($searchService && count($service))
                                                                    <div class="absolute z-50 mt-2 w-full overflow-hidden
                                    rounded-xl border border-gray-200 bg-white shadow-xl">

                                                                        @foreach($service as $serv)

                                                                            <button type="button" wire:key="service-{{ $serv['id'] }}"
                                                                                wire:click="selectService({{ $serv['id'] }})"
                                                                                class="flex w-full items-center justify-between
px-4 py-3 text-right
transition
hover:bg-indigo-50">
                                                                                {{ $serv['name'] }}
                                                                            </button>

                                                                        @endforeach

                                                                    </div>
                                @elseif($searchService && !$serviceSelected)
                                    <div class="mt-1 bg-black border rounded-lg shadow-lg">
                                        <p class="px-4 py-2 text-right text-gray-500">لا يوجد نتائج</p>
                                    </div>
                                @endif

                            </div>



                            @error('form.service_id')
                            <p
class="mt-2 flex items-center gap-2 text-sm text-red-600">

<span>⚠</span>

<span>

{{ $message }}

</span>

</p>
                            @enderror

                            <!-- ++++++++++++++++++++++++++++++++++++++ -->

                @php
    $currentEmployee = auth('employee')->user();
    $isAdmin = $currentEmployee?->hasRole('admin');
@endphp

<div class="relative">

    <label class="mb-2 flex items-center gap-2 text-sm font-semibold text-gray-700">

        💈 الموظف

    </label>


    @if($isAdmin)

        {{-- Admin can select employee --}}

        <input
            type="text"
            wire:model.live.debounce.300ms="searchEmployee"
            placeholder="ابحث عن الموظف..."
            autocomplete="off"
            class="w-full rounded-xl border border-gray-300 bg-gray-50
            px-4 py-3 shadow-sm transition
            focus:border-indigo-500
            focus:bg-white
            focus:ring-2
            focus:ring-indigo-500"
        >

        <input
            type="hidden"
            wire:model="form.employee_id"
        >


        @if($searchEmployee && count($employees))

            <div class="absolute z-50 mt-2 w-full overflow-hidden
                rounded-xl border border-gray-200 bg-white shadow-xl">

                @foreach($employees as $employee)

                    <button
                        type="button"
                        wire:key="employee-{{ $employee['id'] }}"
                        wire:click="selectEmployee({{ $employee['id'] }})"
                        class="flex w-full items-center justify-between
                        px-4 py-3 text-right transition hover:bg-indigo-50"
                    >

                        {{ $employee['name'] }}

                    </button>

                @endforeach

            </div>

        @elseif($searchEmployee && !$EmployeeSelected)

            <div class="px-6 py-5 text-center text-gray-400">

                <div class="text-2xl">
                    🔍
                </div>

                <div class="mt-2">
                    لا توجد نتائج
                </div>

            </div>

        @endif

    @else

        {{-- Employee / Barber --}}
        {{-- Employee is automatically selected --}}

        <div
            class="w-full rounded-xl border border-green-200
            bg-green-50 px-4 py-3"
        >

            <div class="flex items-center justify-between">

                <div>

                    <div class="text-sm text-green-600">
                        الموظف الحالي
                    </div>

                    <div class="font-semibold text-green-800">
                        {{ $currentEmployee->name }}
                    </div>

                </div>

                <div class="text-green-600">
                    <i class="fas fa-check-circle"></i>
                </div>

            </div>

        </div>

        <input
            type="hidden"
            wire:model="form.employee_id"
        >

    @endif


    @error('form.employee_id')

        <p class="mt-2 flex items-center gap-2 text-sm text-red-600">

            <span>⚠</span>

            <span>
                {{ $message }}
            </span>

        </p>

    @enderror

</div>



                            @error('form.employee_id')
                           <p
class="mt-2 flex items-center gap-2 text-sm text-red-600">

<span>⚠</span>

<span>

{{ $message }}

</span>

</p>
                            @enderror

                        </div>
                                        </div>

            </div>

        </div>


                        <div class="mt-6">
 










                            <input type="time" wire:model.live="form.time" class="w-full rounded-xl border border-gray-300 bg-gray-50
px-4 py-3 shadow-sm transition
focus:border-indigo-500
focus:bg-white
focus:ring-2
focus:ring-indigo-500">

                            @error('form.time')

                               <p
class="mt-2 flex items-center gap-2 text-sm text-red-600">

<span>⚠</span>

<span>

{{ $message }}

</span>

</p>

                            @enderror

                            @if($form->time)

                                @if($timeAvailable)

                                    <div class="mt-2 text-green-600">

                                        ✔ الموعد متاح

                                    </div>

                                @else

                                    <div class="mt-2 text-red-600">

                                        ✖ يوجد حجز لهذا الموظف خلال 20 دقيقة.

                                    </div>

                                @endif

                            @endif

                        </div>

                        <div class="mt-8">

                            <h3 class="font-semibold text-gray-700 mb-3">

                                المواعيد المحجوزة اليوم

                            </h3>

                            @if(count($bookedTimes))

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

                                    @foreach($bookedTimes as $booking)

                                        <div class="rounded-lg border border-red-300 bg-red-50 p-3 text-center">

                                            <div class="text-red-700 font-semibold">

                                                {{ $booking['formatted'] }}

                                            </div>

                                            <div class="text-xs text-red-500 mt-1">

                                                محجوز

                                            </div>

                                        </div>

                                    @endforeach

                                </div>

                            @else

                                <div class="rounded-lg border bg-green-50 p-4 text-green-700">

                                    لا توجد حجوزات لهذا الموظف اليوم.

                                </div>

                            @endif

                        </div>

                    </div>



                    {{-- Actions --}}
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('admin.bookings.index') }}"
                            class="rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            الغاء
                        </a>
                        <button type="submit" wire:loading.attr="disabled" @disabled(!$timeAvailable) class="px-4 py-2 bg-blue-600 text-white rounded-lg
           disabled:bg-gray-400
           disabled:cursor-not-allowed
           disabled:opacity-50">
                            إنشاء الحجز
                        </button>
                    </div>
                </div>
    </form>
</div>
</div>