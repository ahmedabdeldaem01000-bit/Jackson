<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        تسجيل الدخول
    </title>

    {{-- AdminLTE --}}
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css"
    >

    {{-- Bootstrap --}}
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
    >

    {{-- Font Awesome --}}
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css"
    >

    {{-- Cairo Font --}}
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: #f4f6f9;
        }

        .login-page {
            min-height: 100vh;
            background: linear-gradient(
                135deg,
                #343a40 0%,
                #1f2327 100%
            );
        }

        .login-box {
            width: 420px;
            max-width: 95%;
        }

        .login-logo a {
            color: #fff;
            font-size: 2rem;
            font-weight: 700;
        }

        .card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.20);
        }

        .card-header {
            background: #343a40;
            color: #fff;
            border-bottom: none;
            text-align: center;
            padding: 25px 20px;
        }

        .card-header h4 {
            margin: 0;
            font-weight: 700;
        }

        .card-body {
            padding: 30px;
        }

        .form-control {
            height: 48px;
            border-radius: 6px;
        }

        .input-group-text {
            border-radius: 0 6px 6px 0;
        }

        .form-control {
            border-radius: 6px 0 0 6px;
        }

        .btn-login {
            height: 48px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 15px;
        }

        .alert {
            border-radius: 6px;
        }

        .brand-logo {
            width: 90px;
            height: 90px;
            object-fit: contain;
            background: #fff;
            border-radius: 50%;
            padding: 8px;
            margin-bottom: 15px;
        }

        .login-footer {
            text-align: center;
            color: #6c757d;
            font-size: 13px;
            margin-top: 20px;
        }

        .custom-control-label {
            font-size: 14px;
        }
    </style>
</head>

<body class="hold-transition login-page">

<div class="login-box">

    {{-- Logo --}}
    <div class="login-logo">
        <a href="{{ route('home') }}">
            <img
                src="{{ asset('images/Untitled-4.png') }}"
                alt="Logo"
                class="brand-logo"
            >
        </a>
    </div>

    {{-- Login Card --}}
    <div class="card">

        <div class="card-header">
            <h4>
                تسجيل الدخول
            </h4>

            <small>
                لوحة الإدارة
            </small>
        </div>

        <div class="card-body">

            {{-- Session Error --}}
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 pr-3">
                        @foreach ($errors->all() as $error)
                            <li>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                action="{{ route('employee.login.submit') }}"
                method="POST"
            >

                @csrf

                {{-- Email --}}
                <div class="form-group">

                    <label for="email">
                        البريد الإلكتروني
                    </label>

                    <div class="input-group">

                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="form-control @error('email') is-invalid @enderror"
                            placeholder="أدخل البريد الإلكتروني"
                            required
                            autofocus
                        >

                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                        </div>

                        @error('email')
                            <span class="invalid-feedback">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>
                </div>

                {{-- Password --}}
                <div class="form-group">

                    <label for="password">
                        كلمة المرور
                    </label>

                    <div class="input-group">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="أدخل كلمة المرور"
                            required
                        >

                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                        </div>

                        @error('password')
                            <span class="invalid-feedback">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>
                </div>

                {{-- Remember Me --}}
                <div class="row align-items-center mb-3">

                    <div class="col-7">

                        <div class="custom-control custom-checkbox">

                            <input
                                type="checkbox"
                                class="custom-control-input"
                                id="remember"
                                name="remember"
                                value="1"
                            >

                            <label
                                class="custom-control-label"
                                for="remember"
                            >
                                تذكرني
                            </label>

                        </div>

                    </div>

                    <div class="col-5 text-left">

                        <a
                            href="#"
                            class="text-muted"
                        >
                            نسيت كلمة المرور؟
                        </a>

                    </div>

                </div>

                {{-- Login Button --}}
                <button
                    type="submit"
                    class="btn btn-dark btn-block btn-login"
                >

                    <i class="fas fa-sign-in-alt ml-1"></i>

                    تسجيل الدخول

                </button>

            </form>

        </div>

        <div class="card-footer bg-white">

            <div class="login-footer">
                نظام إدارة الحجوزات
            </div>

        </div>

    </div>

</div>

{{-- jQuery --}}
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

{{-- Bootstrap --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

{{-- AdminLTE --}}
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

</body>

</html>