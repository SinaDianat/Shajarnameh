<!DOCTYPE html>
<html>
<head>
    <title>ارسال مجدد کد تأیید</title>
    <style>
        body { font-family: Arial, sans-serif; direction: rtl; }
        .container { max-width: 400px; margin: 0 auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; }
        .form-group input { width: 100%; padding: 8px; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <div class="container">
        <h2>ارسال مجدد کد تأیید</h2>
        @if (session('success'))
            <div class="success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif
        <form method="POST" action="{{ route('resend-verification') }}">
            @csrf
            <div class="form-group">
                <label for="email">ایمیل:</label>
                <input type="email" name="email" id="email" value="{{ session('email') }}" required>
            </div>
            <button type="submit">ارسال کد</button>
        </form>
        <p><a href="{{ route('register') }}">بازگشت به ثبت‌نام</a></p>
    </div>
</body>
</html>