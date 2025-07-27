<!DOCTYPE html>
<html>
<head>
    <title>تأیید کد بازنشانی</title>
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
        <h2>تأیید کد بازنشانی</h2>
        @if (session('success'))
            <div class="success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('verify-reset-code') }}">
            @csrf
            <div class="form-group">
                <label for="email">ایمیل:</label>
                <input type="email" name="email" id="email" value="" required >
            </div>
            <div class="form-group">
                <label for="code">کد بازنشانی:</label>
                <input type="text" name="code" id="code" value="{{ old('code') }}" required>
            </div>
            <button type="submit">تأیید کد</button>
        </form>
        <p><a href="{{ route('forgot-password') }}">درخواست کد جدید</a></p>
        <p><a href="{{ route('login') }}">بازگشت به ورود</a></p>
    </div>
</body>
</html>