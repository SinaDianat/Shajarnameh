<!DOCTYPE html>
<html>
<head>
    <title>مدیریت درخواست‌های شجرنامه</title>
    <style>
        body { font-family: Arial, sans-serif; direction: rtl; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: right; }
        th { background-color: #f2f2f2; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h2>مدیریت درخواست‌های شجرنامه</h2>
        @if (session('success'))
            <div class="success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif
        <table>
            <thead>
                <tr>
                    <th>کاربر</th>
                    <th>عنوان</th>
                    <th>وضعیت</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($requests as $request)
                    <tr>
                        <td>{{ $request->user->name }}</td>
                        <td>{{ $request->title }}</td>
                        <td>{{ $request->status == 'pending' ? 'در انتظار' : ($request->status == 'approved' ? 'تأیید شده' : 'رد شده') }}</td>
                        <td>
                            @if ($request->status == 'pending')
                                <form action="{{ route('family_tree_requests.approve', $request->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit">تأیید</button>
                                </form>
                                <form action="{{ route('family_tree_requests.reject', $request->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit">رد</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p><a href="{{ route('dashboard') }}">بازگشت به داشبورد</a></p>
    </div>
</body>
</html>