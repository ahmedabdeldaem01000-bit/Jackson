<div class="space-y-6">

    {{-- Service --}}

    <div class="bg-white rounded-xl shadow">

        <div class="border-b px-6 py-4">

            <h2 class="text-lg font-semibold">

                بيانات الخدمة

            </h2>

        </div>

        <div class="p-6">

            <label class="block text-sm font-medium mb-2">

                اسم الخدمة

            </label>

            <input
                type="text"
                wire:model.blur="name"
                class="w-full rounded-lg border-gray-300"
                placeholder="اسم الخدمة"
            >

            @error('name')

                <p class="mt-2 text-sm text-red-500">

                    {{ $message }}

                </p>

            @enderror

        </div>

    </div>





    {{-- Sub Services --}}

    <div class="bg-white rounded-xl shadow">

        <div
            class="flex items-center justify-between border-b px-6 py-4"
        >

            <h2 class="text-lg font-semibold">

                الخدمات الفرعية

            </h2>

            <button
                type="button"
                wire:click="addSubService"
                class="rounded-lg bg-indigo-600 px-4 py-2 text-white"
            >

                إضافة خدمة

            </button>

        </div>

        <div class="overflow-x-auto">

            <table class="min-w-full">

                <thead>

                <tr>

                    <th class="px-6 py-3 text-right">

                        الاسم

                    </th>

                    <th class="px-6 py-3 text-right">

                        المدة

                    </th>

                    <th class="w-24">
                        السعر
                    </th>

                </tr>

                </thead>

                <tbody>

                @foreach($subServices as $index=>$sup)

                    <tr
                        wire:key="sup-{{ $sup['id'] ?? 'new-'.$index }}"
                        class="border-t"
                    >

                        <td class="px-6 py-4">

                            <input
                                type="text"
                                wire:model.live="subServices.{{ $index }}.name"
                                class="w-full rounded-lg border-gray-300"
                            >

                        </td>

                        <td class="px-6 py-4">

                            <input
                                type="number"
                                wire:model.live="subServices.{{ $index }}.duration"
                                class="w-full rounded-lg border-gray-300"
                            >

                        </td>
                        <td class="px-6 py-4">

                            <input
                                type="text"
                                wire:model.live="subServices.{{ $index }}.price"
                                class="w-full rounded-lg border-gray-300"
                            >

                        </td>

                        <td class="px-6 py-4">

                            <button
                                type="button"
                                wire:click="removeSubService({{ $index }})"
                                class="rounded-lg bg-red-600 px-3 py-2 text-white"
                            >

                                حذف

                            </button>

                        </td>

                    </tr>

                @endforeach

                </tbody>

            </table>

        </div>

    </div>




    <div class="flex justify-end gap-3">

        <a
            href="{{ route('admin.services.index') }}"
            wire:navigate
            class="rounded-lg border px-6 py-2"
        >

            رجوع

        </a>

        <button
            type="submit"
            class="rounded-lg bg-indigo-600 px-6 py-2 text-white"
        >

            {{ $button }}

        </button>

    </div>

</div>