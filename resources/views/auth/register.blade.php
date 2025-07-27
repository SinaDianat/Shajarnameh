<!DOCTYPE html>
<html>
<head>
    <title>ثبت‌نام</title>
    <style>
        body { font-family: Arial, sans-serif; direction: rtl; }
        .container { max-width: 400px; margin: 0 auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; }
        .form-group input { width: 100%; padding: 8px; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h2>ثبت‌نام</h2>
        @if (session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="form-group">
                <label for="name">نام:</label>
                <input type="text" name="name" id="name" required>
            </div>
            <div class="form-group">
                <label for="email">ایمیل:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="password">رمز عبور:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit">ثبت‌نام</button>
        </form>
        <p>قبلاً حساب دارید؟ <a href="{{ route('login') }}">ورود</a></p>
    </div>
</body>
</html>