@extends('layouts.app')

@section('title', 'پنل کاربری')

@section('content')
    <div class="container my-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">پنل کاربری</h5>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- پروفایل کاربر -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">پروفایل</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('user.update_profile') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">نام:</label>
                                    <input type="text" name="name" id="name" class="form-control" value="{{ $user->name }}" required>
                                    @error('name')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">ایمیل:</label>
                                    <input type="email" name="email" id="email" class="form-control" value="{{ $user->email }}" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">رمز عبور جدید:</label>
                                    <input type="password" name="password" id="password" class="form-control">
                                    @error('password')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label">تکرار رمز عبور:</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                                    @error('password_confirmation')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">به‌روزرسانی پروفایل</button>
                        </form>
                    </div>
                </div>

                <!-- درخواست‌های شجره‌نامه -->
                <div class="card mb-4">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">درخواست‌های شجره‌نامه</h6>
                        <a href="{{ route('family_tree_requests.create') }}" class="btn btn-primary btn-sm">ثبت درخواست جدید</a>
                    </div>
                    <div class="card-body">
                        @if ($requests->isEmpty())
                            <p class="text-muted">شما هنوز درخواستی برای شجره‌نامه ثبت نکرده‌اید.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>عنوان</th>
                                            <th>توضیحات</th>
                                            <th>وضعیت</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($requests as $request)
                                            <tr>
                                                <td>{{ $request->title }}</td>
                                                <td>{{ $request->description ?? '-' }}</td>
                                                <td>
                                                    @if ($request->status == 'pending')
                                                        در انتظار
                                                    @elseif ($request->status == 'approved')
                                                        تأیید شده
                                                    @else
                                                        رد شده
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if ($approvedRequest)
                                <a href="{{ route('people.create') }}" class="btn btn-success">ایجاد شجره‌نامه</a>
                            @else
                                <p class="text-muted">برای ایجاد شجره‌نامه، باید درخواست شما توسط ادمین تأیید شود.</p>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- لینک به شجره‌نامه -->
                @if ($approvedRequest)
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">شجره‌نامه شما</h6>
                        </div>
                        <div class="card-body">
                            <a href="{{ route('people.family_tree') }}" class="btn btn-primary">مشاهده و مدیریت شجره‌نامه</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection