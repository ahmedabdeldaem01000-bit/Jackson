<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>تأكيد البريد الإلكتروني</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background:
                radial-gradient(
                    circle at top right,
                    rgba(99, 102, 241, .15),
                    transparent 35%
                ),
                #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .verify-wrapper {
            width: 100%;
            max-width: 460px;
        }

        .verify-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 38px 32px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, .10);
            border: 1px solid #e2e8f0;
        }

        .brand {
            text-align: center;
            margin-bottom: 28px;
        }

        .brand-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 18px;
            border-radius: 20px;
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            font-weight: 800;
            box-shadow: 0 12px 25px rgba(79, 70, 229, .25);
        }

        .brand h1 {
            margin: 0;
            color: #111827;
            font-size: 26px;
        }

        .brand p {
            margin: 10px 0 0;
            color: #64748b;
            font-size: 14px;
            line-height: 1.8;
        }

        .email {
            display: inline-block;
            margin-top: 8px;
            color: #4f46e5;
            font-weight: 700;
        }

        .alert {
            padding: 13px 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.6;
        }

        .alert-success {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .otp-label {
            display: block;
            margin-bottom: 12px;
            color: #334155;
            font-size: 14px;
            font-weight: 700;
        }

        .otp-container {
            display: flex;
            direction: ltr;
            justify-content: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .otp-input {
            width: 52px;
            height: 58px;
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            background: #f8fafc;
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            outline: none;
            transition: .2s;
        }

        .otp-input:focus {
            background: #fff;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, .12);
        }

        .otp-hidden {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .verify-button {
            width: 100%;
            border: 0;
            border-radius: 14px;
            padding: 14px 18px;
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 18px;
            transition: .2s;
        }

        .verify-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, .2);
        }

        .resend-section {
            text-align: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .resend-text {
            color: #64748b;
            font-size: 13px;
        }

        .timer {
            color: #4f46e5;
            font-weight: 700;
        }

        .resend-button {
            border: 0;
            background: transparent;
            color: #4f46e5;
            font-weight: 700;
            cursor: pointer;
            font-size: 14px;
        }

        .back-link {
            display: block;
            margin-top: 22px;
            text-align: center;
            text-decoration: none;
            color: #64748b;
            font-size: 13px;
        }

        .back-link:hover {
            color: #4f46e5;
        }

        @media (max-width: 480px) {

            .verify-card {
                padding: 28px 18px;
            }

            .otp-container {
                gap: 6px;
            }

            .otp-input {
                width: 44px;
                height: 52px;
                font-size: 22px;
            }

        }

    </style>

</head>


<body>

<div class="verify-wrapper">

    <div class="verify-card">

        {{-- Header --}}
        <div class="brand">

            <div class="brand-icon">
                ✓
            </div>

            <h1>
                تأكيد البريد الإلكتروني
            </h1>

            <p>
                أدخل رمز التحقق المرسل إلى بريدك الإلكتروني
                <br>

                <span class="email">
                    {{ $email }}
                </span>
            </p>

        </div>


        {{-- Success --}}
        @if(session('success'))

            <div class="alert alert-success">
                {{ session('success') }}
            </div>

        @endif


        {{-- Error --}}
        @if($errors->has('otp'))

            <div class="alert alert-error">
                {{ $errors->first('otp') }}
            </div>

        @endif


        <form
            method="POST"
            action="{{ route('verification.verify') }}"
            id="verify-form"
        >

            @csrf

            <input
                type="hidden"
                name="email"
                value="{{ $email }}"
            >

            <input
                type="hidden"
                name="otp"
                id="otp-hidden"
                value="{{ old('otp') }}"
            >


            <label class="otp-label">
                رمز التحقق
            </label>


            <div class="otp-container">

                <input
                    type="text"
                    maxlength="1"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    class="otp-input"
                    autofocus
                >

                <input
                    type="text"
                    maxlength="1"
                    inputmode="numeric"
                    class="otp-input"
                >

                <input
                    type="text"
                    maxlength="1"
                    inputmode="numeric"
                    class="otp-input"
                >

                <input
                    type="text"
                    maxlength="1"
                    inputmode="numeric"
                    class="otp-input"
                >

                <input
                    type="text"
                    maxlength="1"
                    inputmode="numeric"
                    class="otp-input"
                >

                <input
                    type="text"
                    maxlength="1"
                    inputmode="numeric"
                    class="otp-input"
                >

            </div>


            <button
                type="submit"
                class="verify-button"
            >
                تأكيد البريد الإلكتروني
            </button>

        </form>


        {{-- Resend --}}
        <div class="resend-section">

            <div
                class="resend-text"
                id="resend-message"
            >
                يمكنك طلب رمز جديد بعد
                <span
                    class="timer"
                    id="timer"
                >
                    60
                </span>
                ثانية
            </div>


            <form
                method="POST"
                action="{{ route('verification.resend') }}"
                id="resend-form"
                style="display:none;"
            >

                @csrf

                <input
                    type="hidden"
                    name="email"
                    value="{{ $email }}"
                >

                <button
                    type="submit"
                    class="resend-button"
                >
                    لم يصلك الرمز؟ إرسال رمز جديد
                </button>

            </form>

        </div>


        <a
            href="{{ route('register') }}"
            class="back-link"
        >
            ← العودة إلى التسجيل
        </a>

    </div>

</div>


<script>

    const inputs = document.querySelectorAll('.otp-input');
    const hiddenInput = document.getElementById('otp-hidden');
    const form = document.getElementById('verify-form');

    /*
    |--------------------------------------------------------------------------
    | OTP Inputs
    |--------------------------------------------------------------------------
    */

    inputs.forEach((input, index) => {

        input.addEventListener('input', function () {

            this.value = this.value.replace(/\D/g, '');

            if (this.value && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }

            updateOtp();

        });


        input.addEventListener('keydown', function (event) {

            if (
                event.key === 'Backspace' &&
                !this.value &&
                index > 0
            ) {
                inputs[index - 1].focus();
            }

        });


        input.addEventListener('paste', function (event) {

            event.preventDefault();

            const pasted = (
                event.clipboardData || window.clipboardData
            )
                .getData('text')
                .replace(/\D/g, '')
                .slice(0, 6);

            pasted.split('').forEach((number, i) => {

                if (inputs[i]) {
                    inputs[i].value = number;
                }

            });

            updateOtp();

            if (inputs[pasted.length - 1]) {
                inputs[pasted.length - 1].focus();
            }

        });

    });


    function updateOtp() {

        let otp = '';

        inputs.forEach(input => {
            otp += input.value;
        });

        hiddenInput.value = otp;

    }


    form.addEventListener('submit', function () {
        updateOtp();
    });


    /*
    |--------------------------------------------------------------------------
    | Resend Countdown
    |--------------------------------------------------------------------------
    */

    let seconds = 60;

    const timer = document.getElementById('timer');

    const resendMessage = document.getElementById('resend-message');

    const resendForm = document.getElementById('resend-form');


    const interval = setInterval(() => {

        seconds--;

        timer.textContent = seconds;

        if (seconds <= 0) {

            clearInterval(interval);

            resendMessage.style.display = 'none';

            resendForm.style.display = 'block';

        }

    }, 1000);

</script>

</body>

</html>