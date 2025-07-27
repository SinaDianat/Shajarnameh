<!DOCTYPE html>
<html>
<head>
    <title>درخواست شجرنامه</title>
    <style>
        body { font-family: Arial, sans-serif; direction: rtl; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; }
        .form-group input, .form-group textarea { width: 100%; padding: 8px; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <div class="container">
        <h2>درخواست ایجاد شجرنامه</h2>
        @if (session('success'))
            <div class="success">{{ session('success') }}</div>
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
        <form method="POST" action="{{ route('family_tree_requests.store') }}">
            @csrf
            <div class="form-group">
                <label for="title">عنوان شجرنامه:</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required>
            </div>
            <div class="form-group">
                <label for="description">توضیحات:</label>
                <textarea name="description" id="description">{{ old('description') }}</textarea>
            </div>
            <button type="submit">ارسال درخواست</button>
        </form>
    </div>
</body>
</html>