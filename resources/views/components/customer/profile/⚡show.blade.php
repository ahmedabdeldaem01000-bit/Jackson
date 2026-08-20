<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new
    #[Layout('layouts.customer')]
    #[Title('الملف الشخصي')]
class extends Component
{
    use WithFileUploads;

    public $customer;

    /*
    |--------------------------------------------------------------------------
    | Profile Information
    |--------------------------------------------------------------------------
    */

    public string $name = '';
    public string $email = '';
    public string $phone = '';

    /*
    |--------------------------------------------------------------------------
    | Avatar
    |--------------------------------------------------------------------------
    */

    public $avatar = null;

    /*
    |--------------------------------------------------------------------------
    | Password
    |--------------------------------------------------------------------------
    */

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        $this->customer = Auth::guard('customer')->user();

        abort_unless($this->customer, 403);

        $this->name = $this->customer->name ?? '';
        $this->email = $this->customer->email ?? '';
        $this->phone = $this->customer->phone ?? '';
    }

    /*
    |--------------------------------------------------------------------------
    | Update Profile
    |--------------------------------------------------------------------------
    */

    public function updateInformation(): void
    {
        $this->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($this->customer->id),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],

            'avatar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ], [
            'name.required' => 'الاسم مطلوب.',
            'name.min' => 'الاسم يجب أن يكون حرفين على الأقل.',

            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'البريد الإلكتروني غير صحيح.',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل.',

            'phone.max' => 'رقم الهاتف غير صحيح.',

            'avatar.image' => 'الملف يجب أن يكون صورة.',
            'avatar.mimes' => 'الصورة يجب أن تكون JPG أو PNG أو WEBP.',
            'avatar.max' => 'حجم الصورة يجب ألا يتجاوز 2MB.',
        ]);

        $this->customer->name = $this->name;
        $this->customer->email = $this->email;
        $this->customer->phone = $this->phone;

        /*
        |--------------------------------------------------------------------------
        | Save Avatar
        |--------------------------------------------------------------------------
        */

        if ($this->avatar) {
            $path = $this->avatar->store(
                'customers',
                'public'
            );

            $this->customer->avatar = $path;
        }

        $this->customer->save();

        $this->customer->refresh();

        $this->reset('avatar');

        session()->flash(
            'profile_success',
            'تم تحديث بيانات الحساب بنجاح.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Password
    |--------------------------------------------------------------------------
    */

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => [
                'required',
                'current_password:customer',
            ],

            'password' => [
                'required',
                'confirmed',
                Password::min(8),
            ],
        ], [
            'current_password.required' =>
                'اكتب كلمة السر الحالية.',

            'current_password.current_password' =>
                'كلمة السر الحالية غير صحيحة.',

            'password.required' =>
                'كلمة السر الجديدة مطلوبة.',

            'password.confirmed' =>
                'تأكيد كلمة السر غير مطابق.',

            'password.min' =>
                'كلمة السر يجب أن تكون 8 أحرف على الأقل.',
        ]);

        $this->customer->password = Hash::make(
            $this->password
        );

        $this->customer->save();

        $this->reset([
            'current_password',
            'password',
            'password_confirmation',
        ]);

        session()->flash(
            'password_success',
            'تم تغيير كلمة السر بنجاح.'
        );
    }
};
?>

<div class="px-4 py-8 sm:px-6 lg:px-8">

    <div class="mx-auto max-w-5xl">

        {{-- ========================================================= --}}
        {{-- Header --}}
        {{-- ========================================================= --}}

        <div class="mb-8">

            <span
                class="inline-flex rounded-full bg-[#f3e5de] px-4 py-2 text-xs font-bold text-[#a56a58]"
            >
                حسابي
            </span>

            <h1 class="mt-4 text-3xl font-bold text-[#4b2a22]">
                الملف الشخصي
            </h1>

            <p class="mt-2 text-sm leading-7 text-gray-500">
                إدارة بياناتك الشخصية وصورة الحساب وكلمة السر.
            </p>

        </div>


        {{-- ========================================================= --}}
        {{-- Profile Success --}}
        {{-- ========================================================= --}}

        @if(session('profile_success'))

            <div
                class="mb-6 flex items-center gap-3 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-semibold text-green-700"
            >

                <span
                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-green-100"
                >
                    ✓
                </span>

                {{ session('profile_success') }}

            </div>

        @endif


        {{-- ========================================================= --}}
        {{-- Password Success --}}
        {{-- ========================================================= --}}

        @if(session('password_success'))

            <div
                class="mb-6 flex items-center gap-3 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-semibold text-green-700"
            >

                <span
                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-green-100"
                >
                    ✓
                </span>

                {{ session('password_success') }}

            </div>

        @endif


        {{-- ========================================================= --}}
        {{-- Personal Information --}}
        {{-- ========================================================= --}}

        <section
            class="overflow-hidden rounded-[2rem] border border-[#eaded8] bg-white shadow-sm"
        >

            {{-- Cover --}}
            <div
                class="h-36 bg-gradient-to-br from-[#5b3025] via-[#754638] to-[#a56a58]"
            ></div>


            <div class="px-6 pb-8 sm:px-8">

                {{-- ================================================= --}}
                {{-- Avatar --}}
                {{-- ================================================= --}}

                <div
                    class="-mt-12 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between"
                >

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end">

                        <div class="shrink-0">

                            @if($avatar)

                                <img
                                    src="{{ $avatar->temporaryUrl() }}"
                                    alt="{{ $customer->name }}"
                                    class="h-24 w-24 rounded-3xl border-4 border-white object-cover shadow-lg"
                                >

                            @elseif($customer->avatar)

                                <img
                                    src="{{ asset($customer->avatar) }}"
                                    alt="{{ $customer->name }}"
                                    class="h-24 w-24 rounded-3xl border-4 border-white object-cover shadow-lg"
                                >

                            @else

                                <div
                                    class="flex h-24 w-24 items-center justify-center rounded-3xl border-4 border-white bg-[#f3e5de] text-3xl font-bold text-[#7d4a3a] shadow-lg"
                                >
                                    {{ strtoupper(mb_substr($customer->name ?? 'U', 0, 1)) }}
                                </div>

                            @endif

                        </div>


                        <div class="pb-1">

                            <h2 class="text-2xl font-bold text-[#4b2a22]">
                                {{ $customer->name }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-500">
                                {{ $customer->email }}
                            </p>

                        </div>

                    </div>


                    {{-- Change Avatar --}}
                    <div>

                        <label
                            for="avatar"
                            class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-[#dfcec6] bg-white px-4 py-3 text-sm font-bold text-[#6d4235] transition hover:bg-[#fffaf8]"
                        >

                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path d="M12 5v14"/>
                                <path d="M5 12h14"/>
                            </svg>

                            تغيير الصورة

                        </label>

                        <input
                            id="avatar"
                            type="file"
                            wire:model="avatar"
                            accept="image/png,image/jpeg,image/webp"
                            class="hidden"
                        >

                    </div>

                </div>


                @error('avatar')

                    <p class="mt-3 text-sm text-red-600">
                        {{ $message }}
                    </p>

                @enderror


                <div
                    wire:loading
                    wire:target="avatar"
                    class="mt-3 text-xs font-semibold text-[#9a6252]"
                >
                    جاري تجهيز الصورة...
                </div>


                {{-- ================================================= --}}
                {{-- Information Form --}}
                {{-- ================================================= --}}

                <form
                    wire:submit="updateInformation"
                    class="mt-8"
                >

                    <div class="grid gap-6 md:grid-cols-2">

                        {{-- Name --}}
                        <div>

                            <label
                                for="name"
                                class="mb-2 block text-sm font-bold text-gray-700"
                            >
                                الاسم
                            </label>

                            <input
                                id="name"
                                type="text"
                                wire:model="name"
                                autocomplete="name"
                                class="w-full rounded-2xl border border-gray-200 bg-[#fcfaf9] px-4 py-3.5 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-[#a56a58] focus:bg-white focus:ring-4 focus:ring-[#a56a58]/10"
                                placeholder="اكتب اسمك"
                            >

                            @error('name')

                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- Email --}}
                        <div>

                            <label
                                for="email"
                                class="mb-2 block text-sm font-bold text-gray-700"
                            >
                                البريد الإلكتروني
                            </label>

                            <input
                                id="email"
                                type="email"
                                wire:model="email"
                                autocomplete="email"
                                class="w-full rounded-2xl border border-gray-200 bg-[#fcfaf9] px-4 py-3.5 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-[#a56a58] focus:bg-white focus:ring-4 focus:ring-[#a56a58]/10"
                                placeholder="example@email.com"
                            >

                            @error('email')

                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- Phone --}}
                        <div>

                            <label
                                for="phone"
                                class="mb-2 block text-sm font-bold text-gray-700"
                            >
                                رقم الهاتف
                            </label>

                            <input
                                id="phone"
                                type="tel"
                                wire:model="phone"
                                autocomplete="tel"
                                class="w-full rounded-2xl border border-gray-200 bg-[#fcfaf9] px-4 py-3.5 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-[#a56a58] focus:bg-white focus:ring-4 focus:ring-[#a56a58]/10"
                                placeholder="01xxxxxxxxx"
                            >

                            @error('phone')

                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                    </div>


                    {{-- Save --}}
                    <div
                        class="mt-8 flex justify-end border-t border-gray-100 pt-6"
                    >

                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="updateInformation"
                            class="inline-flex items-center gap-2 rounded-2xl bg-[#5b3025] px-6 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#713c30] disabled:cursor-not-allowed disabled:opacity-60"
                        >

                            <span
                                wire:loading.remove
                                wire:target="updateInformation"
                            >
                                حفظ التعديلات
                            </span>

                            <span
                                wire:loading
                                wire:target="updateInformation"
                            >
                                جاري الحفظ...
                            </span>

                        </button>

                    </div>

                </form>

            </div>

        </section>


        {{-- ========================================================= --}}
        {{-- Password --}}
        {{-- ========================================================= --}}

        <section
            class="mt-6 rounded-[2rem] border border-[#eaded8] bg-white shadow-sm"
        >

            <div
                class="border-b border-gray-100 px-6 py-6 sm:px-8"
            >

                <div class="flex items-center gap-4">

                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#f8eee9] text-[#9a6252]"
                    >

                        <svg
                            class="h-6 w-6"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <rect
                                x="4"
                                y="10"
                                width="16"
                                height="11"
                                rx="2"
                            />

                            <path d="M8 10V7a4 4 0 0 1 8 0v3"/>

                            <circle cx="12" cy="15" r="1"/>
                        </svg>

                    </div>

                    <div>

                        <h2 class="text-xl font-bold text-[#4b2a22]">
                            كلمة السر
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            غيّر كلمة السر الخاصة بحسابك.
                        </p>

                    </div>

                </div>

            </div>


            <form
                wire:submit="updatePassword"
                class="space-y-6 px-6 py-7 sm:px-8"
            >

                {{-- Current Password --}}
                <div>

                    <label
                        for="current_password"
                        class="mb-2 block text-sm font-bold text-gray-700"
                    >
                        كلمة السر الحالية
                    </label>

                    <input
                        id="current_password"
                        type="password"
                        wire:model="current_password"
                        autocomplete="current-password"
                        class="w-full rounded-2xl border border-gray-200 bg-[#fcfaf9] px-4 py-3.5 text-sm text-gray-800 outline-none transition focus:border-[#a56a58] focus:bg-white focus:ring-4 focus:ring-[#a56a58]/10"
                        placeholder="••••••••"
                    >

                    @error('current_password')

                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>

                    @enderror

                </div>


                <div class="grid gap-6 md:grid-cols-2">

                    {{-- New --}}
                    <div>

                        <label
                            for="password"
                            class="mb-2 block text-sm font-bold text-gray-700"
                        >
                            كلمة السر الجديدة
                        </label>

                        <input
                            id="password"
                            type="password"
                            wire:model="password"
                            autocomplete="new-password"
                            class="w-full rounded-2xl border border-gray-200 bg-[#fcfaf9] px-4 py-3.5 text-sm text-gray-800 outline-none transition focus:border-[#a56a58] focus:bg-white focus:ring-4 focus:ring-[#a56a58]/10"
                            placeholder="••••••••"
                        >

                        @error('password')

                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>


                    {{-- Confirm --}}
                    <div>

                        <label
                            for="password_confirmation"
                            class="mb-2 block text-sm font-bold text-gray-700"
                        >
                            تأكيد كلمة السر
                        </label>

                        <input
                            id="password_confirmation"
                            type="password"
                            wire:model="password_confirmation"
                            autocomplete="new-password"
                            class="w-full rounded-2xl border border-gray-200 bg-[#fcfaf9] px-4 py-3.5 text-sm text-gray-800 outline-none transition focus:border-[#a56a58] focus:bg-white focus:ring-4 focus:ring-[#a56a58]/10"
                            placeholder="••••••••"
                        >

                        @error('password_confirmation')

                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>

                </div>


                <p class="text-xs leading-6 text-gray-400">
                    كلمة السر الجديدة يجب أن تكون 8 أحرف على الأقل.
                </p>


                {{-- Password Save --}}
                <div
                    class="flex justify-end border-t border-gray-100 pt-6"
                >

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="updatePassword"
                        class="inline-flex items-center gap-2 rounded-2xl bg-[#5b3025] px-6 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#713c30] disabled:cursor-not-allowed disabled:opacity-60"
                    >

                        <span
                            wire:loading.remove
                            wire:target="updatePassword"
                        >
                            تغيير كلمة السر
                        </span>

                        <span
                            wire:loading
                            wire:target="updatePassword"
                        >
                            جاري التحديث...
                        </span>

                    </button>

                </div>

            </form>

        </section>

    </div>
</div>