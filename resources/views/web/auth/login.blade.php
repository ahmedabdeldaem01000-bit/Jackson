@extends('web.layouts.app')

@section('content')

<section class="min-h-screen w-full bg-[#ffefdee2] login" id="login">

    <div class="container mx-auto flex min-h-screen flex-col-reverse lg:flex-row">

        <!-- Right Section -->
        <div class="flex w-full items-center justify-center lg:w-1/2">

            <div class="w-full max-w-md">

                <h2 class="mb-2 text-4xl font-bold text-gray-900">
                    Welcome Back
                </h2>

                <p class="mb-8 text-gray-600">
                    Sign in to continue to your account.
                </p>

                <form action="{{route('login')}}" method="post" class="space-y-5">
    @csrf

                    <div>
                        <label class="mb-2 block font-medium text-gray-700">
                            Email
                        </label>

                        <input
                            type="email"
                            placeholder="Enter your email"
                            name="email"
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
                        name="password"
                            type="password"
                            placeholder="Enter your password"
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 outline-none transition focus:border-amber-600"
                        >

                    </div>
                    @error('password')
    <span class="text-danger">{{ $message }}</span>
@enderror

                    <div class="flex items-center justify-between">

                        <label class="flex items-center gap-2 text-sm text-gray-700">

                            <input type="checkbox">

                            Remember me

                        </label>

                        <a
                            href="#"
                            class="text-sm text-amber-700 hover:text-amber-900"
                        >
                            Forgot Password?
                        </a>

                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-amber-700 py-3 font-semibold text-white transition hover:bg-amber-800"
                    >
                        Sign In
                    </button>

                </form>

                <div class="mt-6 text-center">

                    <p class="text-gray-600">

                        Don't have an account?

                        <a
                            href="{{ route('register') }}"
                            class="font-semibold text-amber-700 hover:text-amber-900"
                        >
                            Sign Up
                        </a>

                    </p>

                </div>

            </div>

        </div>

        <!-- Left Section -->
        <div class="hidden w-full items-center justify-center lg:flex lg:w-1/2">

            <img
                src="{{ asset('images/imagelogo.webp') }}"
                alt="Barber"
                class="w-[80%] rounded-3xl shadow-2xl object-cover"
            >

        </div>

    </div>

</section>

@endsection