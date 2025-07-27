<!DOCTYPE html>
<html>
<head>
    <title>ورود</title>
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
        <h2>ورود</h2>
        @if (session('success'))
            <div class="success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="email">ایمیل:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="password">رمز عبور:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit">ورود</button>
        </form>
        <p>حساب ندارید؟ <a href="{{ route('register') }}">ثبت‌نام</a></p>
        <p><a href="{{ url('/forgot-password') }}">فراموشی رمز عبور</a></p>
    </div>
</body>
</html>