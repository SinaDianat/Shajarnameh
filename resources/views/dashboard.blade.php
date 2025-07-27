<!DOCTYPE html>
<html>
<head>
    <title>داشبورد</title>
    <style>
        body { font-family: Arial, sans-serif; direction: rtl; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h2>خوش آمدید، {{ Auth::user()->name }}</h2>
        @if (session('success'))
            <div class="success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif
        @if (Auth::user()->is_admin)
            <p><a href="{{ route('family_tree_requests.index') }}">مدیریت درخواست‌های شجرنامه</a></p>
        @else
            <p><a href="{{ route('family_tree_requests.create') }}">درخواست ایجاد شجرنامه</a></p>
        @endif
        <p><a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">خروج</a></p>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</body>
</html>