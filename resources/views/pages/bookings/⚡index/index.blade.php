 <div class="px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">الحجوزات</h1>
            <p class="mt-2 text-sm text-gray-700">
              قائمة بجميع الحجوزات تتضمن أسماءهم و موعيدهم , بريدهم الإلكتروني  و خدماتهم و العاملين.

            </p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none flex gap-2">
       
  

            <a href="{{ route('bookings.create') }}"
                class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                اضافة حجز جديد
            </a>
        </div>
        @if (session()->has('error'))
            <div class="mt-4 rounded-md bg-red-50 p-4">
                <div class="flex">
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">
                            {{ session('error') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Flash Message --}}
    @if (session()->has('message'))
        <div class="mt-4 rounded-md bg-green-50 p-4">
            <div class="flex">
                <div class="shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">
                        {{ session('message') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Filters --}}
    <div class="mt-8 flex flex-col md:flex-row gap-4">
        {{-- Search --}}
        <div class="flex-1">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="ابحث عن اسم عميل..."
                class="py-2.5 sm:py-3 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600">
        </div>
 

        {{-- Status Filter --}}
        <div class="w-full md:w-40">
            <select wire:model.live="status"
                class="py-3 px-4 pe-9 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600">
                <option value="">حاله الحجز</option>
                <option value="completed">اكتمل</option>
                <option value="pending">في الانتظار</option>
                <option value="cancelled">الغاء</option>
            </select>
        </div>

        

        {{-- Reset Filters --}}
        <button wire:click="resetFilters"
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            الافتراضي
        </button>
    </div>

    {{-- Bulk Actions --}}
    @if(count($selected) > 0)
        <div class="mt-4 bg-indigo-50 p-4 rounded-md flex items-center justify-between">
            <span class="text-sm text-indigo-700">
                {{ count($selected) }} booking(s) selected
            </span>
            <div class="flex gap-2">
            
                <button wire:click="bulkDelete" wire:confirm="Are you sure you want to delete the selected bookings?"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200">
                    Delete Selected
                </button>
            </div>
        </div>
    @endif

    {{-- Table --}}
    <div class="mt-8 flex flex-col w-full">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="relative w-12 px-6 sm:w-16 sm:px-8">
                                    <input type="checkbox" wire:model.live="selectAll"
                                        class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 sm:left-6">
                                </th>


                                {{-- Sortable Name Column --}}
                                <th wire:click="sortBy('name')"
                                    class="cursor-pointer px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    <div class="flex text-center items-center gap-2">
                                        الاسم
                                        @if($sortField === 'name')
                                            <span>
                                                @if($sortDirection === 'asc')
                                                    ↑
                                                @else
                                                    ↓
                                                @endif
                                            </span>
                                        @endif
                                    </div>
                                </th>

                                <th scope="col" class="px-3 py-3.5   text-sm font-semibold text-gray-900">
                                    الايميل
                                </th>
                                <th scope="col" class="px-3 py-3.5   text-sm font-semibold text-gray-900">
                                    رقم الهاتف
                                </th>
                                <th scope="col" class="px-3 py-3.5   text-sm font-semibold text-gray-900">
                                    وقت الحجز
                                </th>

                       

                        

                                <th scope="col" class="px-3 py-3.5  text-sm font-semibold text-gray-900">
                                    الحاله
                                </th>

                                <th scope="col" class=" text-center  py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="">اوامر</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($this->bookings as $booking)
                                <tr wire:key="booking-{{ $booking->id }}" class="hover:bg-gray-50">
                                    <td class="relative w-12 px-6 sm:w-16 sm:px-8">
                                        <input type="checkbox" wire:model.live="selected" value="{{ $booking->id }}"
                                            class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 sm:left-6">
                                    </td>

                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        <div class="font-medium text-gray-900">
                                            {{ $booking->user->name }}
                                        </div>
                                    </td>

                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ $booking->user->email }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ $booking->user->phone }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ $booking->time }}
                                    </td>
  

                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        <span
                                            class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 
                                                        {{ $booking->status === 'completed' ? 'bg-green-800 text-white' : 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </td>

                                    <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <div class="flex justify-center gap-2">
                                           
                                            <button wire:click="cancelled({{ $booking->id }})"
                                                class="text-white   bg-yellow-500 hover:bg-yellow-600 p-2 rounded-lg">
                                                    cancelled
                                            </button>
                                            <button wire:click="completed({{ $booking->id }})"
                                                class="text-white   bg-green-600 hover:bg-green-900 p-2 rounded-lg">
                                                completed
                                            </button>
                                            <button wire:click="confirmDelete({{ $booking->id }})"
                                                class="text-white   bg-red-600 hover:bg-red-900 p-2 rounded-lg">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-3 py-8 text-center text-sm text-gray-500">
                                        No employees found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $this->bookings->links() }}
    </div>

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div
                        class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">
                                    Delete Employee
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Are you sure you want to delete this employee? This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                            <button wire:click="deleteBooking" type="button"
                                class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">
                                Delete
                            </button>
                            <button wire:click="closeDeleteModal" type="button"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>