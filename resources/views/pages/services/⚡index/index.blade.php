<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold">
                الخدمات
            </h1>

            <p class="text-sm text-gray-500">
                إدارة الخدمات والخدمات الفرعية
            </p>
        </div>

        <a
            href="{{ route('services.create') }}"
            wire:navigate
            class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
        >
            إضافة خدمة
        </a>
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
    <div class="bg-white rounded-xl shadow p-5">

        <div class="grid md:grid-cols-3 gap-4">

            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                wire:input="updateSearch"
                placeholder="بحث..."
                class="rounded-lg border-gray-300"
            >

            <input
                type="text"
                wire:model.live="name"
                wire:change="updateName"
                placeholder="اسم الخدمة"
                class="rounded-lg border-gray-300"
            >

            <input
                type="text"
                wire:model.live="sup_service"
                wire:change="updateSupService"
                placeholder="الخدمة الفرعية"
                class="rounded-lg border-gray-300"
            >

        </div>

        <div class="mt-4 flex gap-3">

            <button
                wire:click="resetFilters"
                class="px-4 py-2 rounded-lg border"
            >
                إعادة تعيين
            </button>


            @if(count($selected))

                <button
                    wire:click="bulkDelete"
                    wire:confirm="هل تريد حذف الخدمات المحددة؟"
                    class="px-4 py-2 rounded-lg bg-red-600 text-white"
                >
                    حذف المحدد
                </button>

            @endif

        </div>

    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">

        <table class="min-w-full">

            <thead class="bg-gray-50">

            <tr>

                <th class="p-4 w-10">

                    <input
                        type="checkbox"
                        wire:model.live="selectAll"
                    >

                </th>

                <th
                    wire:click="sortBy('name')"
                    class="cursor-pointer text-right p-4"
                >
                    الخدمة
                </th>

                <th class="text-right p-4">
                    الخدمات الفرعية
                </th>

                <th class="text-center p-4">
                    العدد
                </th>

                <th class="text-center p-4">
                    الإجراءات
                </th>

            </tr>

            </thead>

            <tbody>

            @forelse($this->services as $service)

                <tr class="border-t">

                    <td class="p-4">

                        <input
                            type="checkbox"
                            value="{{ $service->id }}"
                            wire:model.live="selected"
                        >

                    </td>

                    <td class="p-4 font-semibold">

                        {{ $service->name }}

                    </td>

                    <td class="p-4">

                        <div class="flex flex-wrap gap-2">

                            @foreach($service->subServices as $sup)

                                <span
                                    class="px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 text-sm"
                                >
                                    {{ $sup->name }}
                                </span>

                            @endforeach

                        </div>

                    </td>

                    <td class="text-center">

                        {{ $service->subServices->count() }}

                    </td>

                    <td>

                        <div class="flex justify-center gap-2">

                            <a
                                href="{{ route('services.edit',$service) }}"
                                wire:navigate
                                class="px-3 py-1 bg-blue-500 text-black rounded"
                            >
                                تعديل
                            </a>

                            <button
                                wire:click="confirmDelete({{ $service->id }})"
                                class="px-3 py-1 bg-red-600 text-white rounded"
                            >
                                حذف
                            </button>

                        </div>

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="5" class="text-center p-8 text-gray-500">

                        لا توجد خدمات

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

    {{-- Pagination --}}

    <div>

        {{ $this->services->links() }}

    </div>

    {{-- Delete Modal --}}
 

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
                            <button wire:click="deleteService" type="button"
                                class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">
                                Delete
                            </button>
                            <button  
                              wire:click="$set('showDeleteModal', false)"
                            type="button"
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