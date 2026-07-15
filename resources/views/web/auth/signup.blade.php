@extends('web.layouts.app')

@section('content')

<section class="w-full bg-[#ffefdee2] py-12 lg:py-16">

    <div class="container mx-auto flex items-center justify-center flex-col-reverse lg:flex-row gap-10 py-10">

        <!-- Left Section -->
        <div class="hidden w-full items-center justify-center lg:flex lg:w-1/2">

            <img
                src="{{ asset('images/imagelogo.webp') }}"
                alt="Barber"
             class="w-[60%] max-w-sm rounded-3xl object-cover shadow-xl"
            >

        </div>

        <!-- Right Section -->
        <div class="flex w-full items-center justify-center lg:w-1/2">

            <div class="w-full max-w-sm">

             <h2 class="mb-2 text-3xl font-bold">
                    Create Account
                </h2>

                <p class="mb-8 text-gray-600">
                    Join us and book your appointments with ease.
                </p>

                <form action="{{ route('register') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label class="mb-2 block font-medium text-gray-700">
                            Full Name
                        </label>

                        <input
                            type="text"
                            name="name"
                            placeholder="Enter your full name"
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 outline-none transition focus:border-amber-600"
                        >
                                         @error('name')
    <span class="text-danger">{{ $message }}</span>
@enderror
                    </div>

                    <div>
                        <label class="mb-2 block font-medium text-gray-700">
                            Email
                        </label>

                        <input
                            type="email"
                            name="email"
                            placeholder="Enter your email"
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 outline-none transition focus:border-amber-600"
                        >
                                         @error('email')
    <span class="text-danger">{{ $message }}</span>
@enderror
                    </div>

               

                    <div>
                        <label class="mb-2 block font-medium text-gray-700">
                            Password
                        </label>

                        <input
                            type="password"
                            name="password"
                            placeholder="Create a password"
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 outline-none transition focus:border-amber-600"
                        >
                                         @error('password')
    <span class="text-danger">{{ $message }}</span>
@enderror
                    </div>

                    <div>
                        <label class="mb-2 block font-medium text-gray-700">
                            Confirm Password
                        </label>

                        <input
                            type="password"
                            name="password_confirmation"
                            placeholder="Confirm your password"
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 outline-none transition focus:border-amber-600"
                        >
                                         @error('password_confirmation')
    <span class="text-danger">{{ $message }}</span>
@enderror
                    </div>

                    <div class="flex items-start gap-2">

                        <input
                            id="terms"
                            type="checkbox"
                            class="mt-1"
                        >

                        <label for="terms" class="text-sm text-gray-600">
                            I agree to the
                            <a href="#" class="font-medium text-amber-700 hover:text-amber-900">
                                Terms & Conditions
                            </a>
                        </label>

                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-amber-700 py-3 font-semibold text-white transition hover:bg-amber-800"
                    >
                        Create Account
                    </button>

                </form>

                <div class="mt-6 text-center">

                    <p class="text-gray-600">

                        Already have an account?

                        <a
                            href="{{ route('login') }}"
                            class="font-semibold text-amber-700 hover:text-amber-900"
                        >
                            Sign In
                        </a>

                    </p>

                </div>

            </div>

        </div>

    </div>

</section>

@endsection