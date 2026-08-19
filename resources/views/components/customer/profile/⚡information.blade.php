<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('معلومات الحساب')] class extends Component
{
    use WithFileUploads;

    public $customer;

    public string $name = '';
    public string $email = '';
    public string $phone = '';

    public $avatar = null;

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
            'name.min' => 'الاسم لازم يكون حرفين على الأقل.',

            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'اكتب بريد إلكتروني صحيح.',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل.',

            'phone.max' => 'رقم الهاتف طويل بشكل غير صحيح.',

            'avatar.image' => 'الملف لازم يكون صورة.',
            'avatar.mimes' => 'الصورة لازم تكون JPG أو PNG أو WEBP.',
            'avatar.max' => 'حجم الصورة يجب ألا يتجاوز 2MB.',
        ]);

        $this->customer->name = $this->name;
        $this->customer->email = $this->email;
        $this->customer->phone = $this->phone;

        if ($this->avatar) {

            $path = $this->avatar->store(
                'customers',
                'public'
            );

            $this->customer->avatar = $path;
        }

        $this->customer->save();

        $this->reset('avatar');

        session()->flash(
            'success',
            'تم تحديث بيانات الحساب بنجاح.'
        );
    }

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
            'current_password.required' => 'اكتب كلمة السر الحالية.',
            'current_password.current_password' => 'كلمة السر الحالية غير صحيحة.',
            'password.required' => 'كلمة السر الجديدة مطلوبة.',
            'password.confirmed' => 'تأكيد كلمة السر غير مطابق.',
            'password.min' => 'كلمة السر لازم تكون 8 أحرف على الأقل.',
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
<div
    dir="rtl"
    class="min-h-screen bg-[#faf8f6] py-8 sm:py-12"
>
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

        {{-- ========================================================= --}}
        {{-- Header --}}
        {{-- ========================================================= --}}

        <div class="mb-8">

            <span
                class="inline-flex items-center rounded-full bg-[#f3e5de] px-4 py-2 text-xs font-bold text-[#a56a58]"
            >
                إعدادات الحساب
            </span>

            <h1
                class="mt-4 text-3xl font-bold tracking-tight text-[#4b2a22] sm:text-4xl"
            >
                معلوماتي
            </h1>

            <p class="mt-2 text-sm leading-7 text-gray-500">
                حدّث بيانات حسابك أو غيّر كلمة السر الخاصة بك.
            </p>

        </div>


        {{-- ========================================================= --}}
        {{-- Success Message --}}
        {{-- ========================================================= --}}

        @if(session('success'))

            <div
                class="mb-6 flex items-center gap-3 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-semibold text-green-700"
            >

                <div
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-green-100"
                >
                    ✓
                </div>

                <span>
                    {{ session('success') }}
                </span>

            </div>

        @endif


        @if(session('password_success'))

            <div
                class="mb-6 flex items-center gap-3 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-semibold text-green-700"
            >

                <div
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-green-100"
                >
                    ✓
                </div>

                <span>
                    {{ session('password_success') }}
                </span>

            </div>

        @endif


        {{-- ========================================================= --}}
        {{-- Account Information --}}
        {{-- ========================================================= --}}

        <section
            class="rounded-[2rem] border border-[#eaded8] bg-white shadow-sm"
        >

            <div
                class="border-b border-[#f0e5df] px-6 py-6 sm:px-8"
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
                            <circle cx="12" cy="8" r="3.5"/>
                            <path d="M5 21a7 7 0 0 1 14 0"/>
                        </svg>
                    </div>

                    <div>
                        <h2 class="text-xl font-bold text-[#4b2a22]">
                            بيانات الحساب
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            المعلومات الأساسية الخاصة بحسابك.
                        </p>
                    </div>

                </div>

            </div>


            <form
                wire:submit="updateInformation"
                class="space-y-6 px-6 py-7 sm:px-8"
            >
            {{-- Profile Image --}}
<div class="flex flex-col gap-5 rounded-2xl bg-[#fcfaf9] p-5 sm:flex-row sm:items-center">

    <div class="shrink-0">

@if($avatar)
    <img
        src="{{ $avatar->temporaryUrl() }}"
        alt="{{ $customer->name }}"
        class="h-24 w-24 rounded-3xl object-cover ring-4 ring-[#f3e5de]"
    >
@elseif($customer->avatar)
    <img
        src="{{ Storage::url($customer->avatar) }}"
        alt="{{ $customer->name }}"
        class="h-24 w-24 rounded-3xl object-cover ring-4 ring-[#f3e5de]"
    >
@else
    <div
        class="flex h-24 w-24 items-center justify-center rounded-3xl bg-[#f3e5de] text-3xl font-bold text-[#8b5747]"
    >
        {{ strtoupper(mb_substr($customer->name ?? 'U', 0, 1)) }}
    </div>
@endif

    </div>


    <div class="flex-1">

        <h3 class="font-bold text-[#4b2a22]">
            صورة الحساب
        </h3>

        <p class="mt-1 text-sm leading-6 text-gray-500">
            اختار صورة شخصية للحساب.
            الحد الأقصى 2MB.
        </p>

        <label
            for="avatar"
            class="mt-4 inline-flex cursor-pointer items-center rounded-xl border border-[#e1d0c8] bg-white px-4 py-2.5 text-sm font-bold text-[#6d4235] transition hover:bg-[#fffaf8]"
        >
            تغيير الصورة
        </label>

        <input
            id="avatar"
            type="file"
            wire:model="avatar"
            accept="image/png,image/jpeg,image/webp"
            class="hidden"
        >

        @error('avatar')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror

        <div
            wire:loading
            wire:target="avatar"
            class="mt-2 text-xs font-semibold text-[#9a6252]"
        >
            جاري تجهيز الصورة...
        </div>

    </div>

</div>

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
        placeholder="01xxxxxxxxx"
        class="w-full rounded-2xl border border-gray-200 bg-[#fcfaf9] px-4 py-3.5 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-[#a56a58] focus:bg-white focus:ring-4 focus:ring-[#a56a58]/10"
    >

    @error('phone')
        <p class="mt-2 text-sm text-red-600">
            {{ $message }}
        </p>
    @enderror

</div>


                {{-- Save --}}
                <div class="flex justify-end border-t border-gray-100 pt-6">

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="updateInformation"
                        class="inline-flex items-center gap-2 rounded-2xl bg-[#5b3025] px-6 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#713c30] disabled:cursor-not-allowed disabled:opacity-60"
                    >

                        <span wire:loading.remove wire:target="updateInformation">
                            حفظ التعديلات
                        </span>

                        <span wire:loading wire:target="updateInformation">
                            جاري الحفظ...
                        </span>

                    </button>

                </div>

            </form>

        </section>


        {{-- ========================================================= --}}
        {{-- Password --}}
        {{-- ========================================================= --}}

        <section
            class="mt-6 rounded-[2rem] border border-[#eaded8] bg-white shadow-sm"
        >

            <div
                class="border-b border-[#f0e5df] px-6 py-6 sm:px-8"
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
                            تغيير كلمة السر
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            استخدم كلمة سر قوية للحفاظ على أمان حسابك.
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


                {{-- New Password --}}
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

                    <p class="mt-2 text-xs text-gray-400">
                        يجب أن تكون كلمة السر 8 أحرف على الأقل.
                    </p>

                    @error('password')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Confirm Password --}}
                <div>

                    <label
                        for="password_confirmation"
                        class="mb-2 block text-sm font-bold text-gray-700"
                    >
                        تأكيد كلمة السر الجديدة
                    </label>

                    <input
                        id="password_confirmation"
                        type="password"
                        wire:model="password_confirmation"
                        autocomplete="new-password"
                        class="w-full rounded-2xl border border-gray-200 bg-[#fcfaf9] px-4 py-3.5 text-sm text-gray-800 outline-none transition focus:border-[#a56a58] focus:bg-white focus:ring-4 focus:ring-[#a56a58]/10"
                        placeholder="••••••••"
                    >

                </div>


                {{-- Save Password --}}
                <div class="flex justify-end border-t border-gray-100 pt-6">

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="updatePassword"
                        class="inline-flex items-center gap-2 rounded-2xl bg-[#5b3025] px-6 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#713c30] disabled:cursor-not-allowed disabled:opacity-60"
                    >

                        <span wire:loading.remove wire:target="updatePassword">
                            تغيير كلمة السر
                        </span>

                        <span wire:loading wire:target="updatePassword">
                            جاري التحديث...
                        </span>

                    </button>

                </div>

            </form>

        </section>

    </div>
</div>