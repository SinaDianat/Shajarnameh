<!DOCTYPE html>
<html>
<head>
    <title>بازنشانی رمز عبور</title>
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
        <h2>بازنشانی رمز عبور</h2>
        @if (session('success'))
            <div class="success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif
        <form method="POST" action="{{ route('reset-password') }}">
            @csrf
            <div class="form-group">
                <label for="email">ایمیل:</label>
                <input type="email" name="email" id="email" value="{{ session('email') ?? old('email') }}" required readonly>
            </div>
            <div class="form-group">
                <label for="code">کد بازنشانی:</label>
                <input type="text" name="code" id="code" value="{{ session('code') ?? old('code') }}" required>
            </div>
            <div class="form-group">
                <label for="password">رمز عبور جدید:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit">بازنشانی</button>
        </form>
        <p><a href="{{ route('login') }}">بازگشت به ورود</a></p>
    </div>
</body>
</html>