<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>تأكيد البريد الإلكتروني</title>
</head>

<body style="margin:0; padding:0; background:#f1f5f9; font-family:Arial, Helvetica, sans-serif;">

    <table
        width="100%"
        cellpadding="0"
        cellspacing="0"
        border="0"
        style="background:#f1f5f9; padding:40px 15px;"
    >
        <tr>
            <td align="center">

                <table
                    width="100%"
                    cellpadding="0"
                    cellspacing="0"
                    border="0"
                    style="
                        max-width:560px;
                        background:#ffffff;
                        border-radius:20px;
                        overflow:hidden;
                        box-shadow:0 10px 35px rgba(15,23,42,.08);
                    "
                >

                    {{-- Header --}}
                    <tr>
                        <td
                            style="
                                background:linear-gradient(135deg,#4f46e5,#6366f1);
                                padding:32px 25px;
                                text-align:center;
                            "
                        >

                            <div
                                style="
                                    width:64px;
                                    height:64px;
                                    line-height:64px;
                                    margin:0 auto 15px;
                                    background:rgba(255,255,255,.15);
                                    border-radius:18px;
                                    color:#ffffff;
                                    font-size:28px;
                                    font-weight:bold;
                                "
                            >
                                ✓
                            </div>

                            <h1
                                style="
                                    margin:0;
                                    color:#ffffff;
                                    font-size:26px;
                                    font-weight:700;
                                "
                            >
                                تأكيد البريد الإلكتروني
                            </h1>

                            <p
                                style="
                                    margin:10px 0 0;
                                    color:rgba(255,255,255,.85);
                                    font-size:14px;
                                "
                            >
                                خطوة واحدة وتقدر تكمل إنشاء حسابك
                            </p>

                        </td>
                    </tr>


                    {{-- Content --}}
                    <tr>
                        <td style="padding:40px 35px 25px;">

                            <h2
                                style="
                                    margin:0 0 12px;
                                    color:#111827;
                                    font-size:21px;
                                    text-align:center;
                                "
                            >
                                رمز التحقق الخاص بك
                            </h2>

                            <p
                                style="
                                    margin:0;
                                    color:#64748b;
                                    font-size:15px;
                                    line-height:1.8;
                                    text-align:center;
                                "
                            >
                                استخدم الرمز التالي لتأكيد بريدك الإلكتروني.
                                <br>
                                الرمز صالح لمدة <strong>5 دقائق</strong>.
                            </p>


                            {{-- OTP --}}
                            <div
                                style="
                                    margin:30px auto;
                                    padding:18px 20px;
                                    background:#eef2ff;
                                    border:1px solid #c7d2fe;
                                    border-radius:16px;
                                    text-align:center;
                                "
                            >

                                <div
                                    style="
                                        color:#6366f1;
                                        font-size:12px;
                                        font-weight:700;
                                        margin-bottom:8px;
                                    "
                                >
                                    رمز التحقق
                                </div>

                                <div
                                    style="
                                        color:#1e1b4b;
                                        font-size:34px;
                                        line-height:1;
                                        font-weight:800;
                                        letter-spacing:10px;
                                        direction:ltr;
                                    "
                                >
                                    {{ $otp }}
                                </div>

                            </div>


                            {{-- Security --}}
                            <div
                                style="
                                    padding:15px 16px;
                                    background:#f8fafc;
                                    border-radius:12px;
                                    color:#64748b;
                                    font-size:13px;
                                    line-height:1.8;
                                    text-align:right;
                                "
                            >
                                <strong style="color:#334155;">
                                    ملاحظة أمنية:
                                </strong>
                                لا تشارك رمز التحقق مع أي شخص.
                                فريق الدعم لن يطلب منك هذا الرمز.
                            </div>


                            <p
                                style="
                                    margin:28px 0 0;
                                    color:#94a3b8;
                                    font-size:12px;
                                    line-height:1.8;
                                    text-align:center;
                                "
                            >
                                لو أنت لم تطلب إنشاء حساب، تجاهل الرسالة.
                            </p>

                        </td>
                    </tr>


                    {{-- Footer --}}
                    <tr>
                        <td
                            style="
                                padding:20px 25px;
                                background:#f8fafc;
                                border-top:1px solid #e2e8f0;
                                text-align:center;
                            "
                        >

                            <p
                                style="
                                    margin:0;
                                    color:#64748b;
                                    font-size:12px;
                                "
                            >
                                {{ config('app.name') }}
                            </p>

                            <p
                                style="
                                    margin:5px 0 0;
                                    color:#94a3b8;
                                    font-size:11px;
                                "
                            >
                                هذه رسالة تلقائية، برجاء عدم الرد عليها.
                            </p>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>