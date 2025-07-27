@extends('layouts.app')

@section('title', 'پنل ادمین')

@section('content')
    <h1>پنل ادمین</h1>
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#site-management">مدیریت سایت</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#users">مدیریت کاربران</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#family-tree-requests">مدیریت درخواست‌های شجرنامه</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#approved-family-trees">شجرنامه‌های ثبت‌شده</a>
        </li>
    </ul>

    <div class="tab-content mt-3">
        <!-- مدیریت سایت -->
        <div class="tab-pane fade show active" id="site-management">
            <h2>مدیریت سایت</h2>
            <p>در این بخش می‌توانید تنظیمات کلی سایت، آمار کاربران، یا سایر ابزارهای مدیریتی را مشاهده و مدیریت کنید.</p>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">آمار کلی</h5>
                    <p class="card-text">تعداد کاربران: {{ $users->count() }}</p>
                    <p class="card-text">تعداد درخواست‌های شجره‌نامه: {{ $requests->count() }}</p>
                    <p class="card-text">تعداد شجره‌نامه‌های تأییدشده: {{ $approvedRequests->count() }}</p>
                </div>
            </div>
        </div>

        <!-- مدیریت کاربران -->
        <div class="tab-pane fade" id="users">
            <h2>مدیریت کاربران</h2>
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>نام</th>
                        <th>ایمیل</th>
                        <th>ادمین</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->is_admin ? 'بله' : 'خیر' }}</td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-user" data-id="{{ $user->id }}" data-bs-toggle="modal" data-bs-target="#editUserModal">ویرایش</button>
                                <form action="{{ route('admin.delete_user', $user->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('آیا مطمئن هستید؟')">حذف</button>
                                </form>
                                <form action="{{ route('admin.toggle_admin', $user->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $user->is_admin ? 'btn-warning' : 'btn-success' }}">
                                        {{ $user->is_admin ? 'لغو ادمین' : 'ادمین کردن' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- مدیریت درخواست‌های شجرنامه -->
        <div class="tab-pane fade" id="family-tree-requests">
            <h2>مدیریت درخواست‌های شجرنامه</h2>
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>کاربر</th>
                        <th>عنوان</th>
                        <th>توضیحات</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requests as $request)
                        <tr>
                            <td>{{ $request->user->name }}</td>
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
                            <td>
                                @if ($request->status == 'pending')
                                    <form action="{{ route('family_tree_requests.approve', $request->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">تأیید</button>
                                    </form>
                                    <form action="{{ route('family_tree_requests.reject', $request->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger">رد</button>
                                    </form>
                                @endif
                                <button class="btn btn-sm btn-primary view-family-tree" data-id="{{ $request->id }}" data-bs-toggle="modal" data-bs-target="#viewFamilyTreeModal">مشاهده شجرنامه</button>
                                <button class="btn btn-sm btn-primary grant-access" data-id="{{ $request->id }}" data-bs-toggle="modal" data-bs-target="#grantAccessModal">اعطای دسترسی</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- شجرنامه‌های ثبت‌شده -->
        <div class="tab-pane fade" id="approved-family-trees">
            <h2>شجرنامه‌های ثبت‌شده</h2>
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if ($approvedRequests->isEmpty())
                <p>هیچ شجره‌نامه تأییدشده‌ای وجود ندارد.</p>
            @else
                @foreach ($approvedRequests as $request)
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5>شجرنامه: {{ $request->title }}</h5>
                            <p>کاربر: {{ $request->user->name }} | توضیحات: {{ $request->description ?? '-' }}</p>
                            <form action="{{ route('admin.delete_family_tree', $request->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('آیا مطمئن هستید؟ این عمل کل شجره‌نامه و افراد مرتبط را حذف می‌کند.')">حذف شجره‌نامه</button>
                            </form>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>نام</th>
                                        <th>جنسیت</th>
                                        <th>شهر محل تولد</th>
                                        <th>شغل</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $people = \App\Models\People::whereIn('id', $request->user->access_ids ?? [])->with(['cityOfBirth', 'occupation'])->get();
                                    @endphp
                                    @if ($people->isEmpty())
                                        <tr><td colspan="5">هیچ فردی در این شجرنامه وجود ندارد.</td></tr>
                                    @else
                                        @foreach ($people as $person)
                                            <tr>
                                                <td>{{ $person->name }}</td>
                                                <td>{{ $person->gender == 'male' ? 'مرد' : 'زن' }}</td>
                                                <td>{{ $person->cityOfBirth?->name ?? '-' }}</td>
                                                <td>{{ $person->occupation?->name ?? '-' }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary edit-person" data-id="{{ $person->id }}" data-bs-toggle="modal" data-bs-target="#editPersonModal">ویرایش</button>
                                                    <form action="{{ route('admin.delete_person', $person->id) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('آیا مطمئن هستید؟ حذف فرد ممکن است روابط را تحت تأثیر قرار دهد.')">حذف</button>
                                                    </form>
                                                    <button class="btn btn-sm btn-primary add-parent" data-id="{{ $person->id }}" data-type="father" data-bs-toggle="modal" data-bs-target="#addParentModal">افزودن پدر</button>
                                                    <button class="btn btn-sm btn-primary add-parent" data-id="{{ $person->id }}" data-type="mother" data-bs-toggle="modal" data-bs-target="#addParentModal">افزودن مادر</button>
                                                    <button class="btn btn-sm btn-primary add-child" data-id="{{ $person->id }}" data-bs-toggle="modal" data-bs-target="#addChildModal">افزودن فرزند</button>
                                                    <button class="btn btn-sm btn-primary add-partner" data-id="{{ $person->id }}" data-bs-toggle="modal" data-bs-target="#addPartnerModal">افزودن همسر</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <!-- مودال ویرایش کاربر -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">ویرایش کاربر</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm" method="POST">
                        @csrf
                        <input type="hidden" name="user_id" id="user_id">
                        <div class="mb-3">
                            <label for="name" class="form-label">نام:</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">ایمیل:</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="is_admin" class="form-label">ادمین:</label>
                            <input type="checkbox" name="is_admin" id="is_admin" value="1">
                        </div>
                        <button type="submit" class="btn btn-primary">ذخیره</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال اعطای دسترسی -->
    <div class="modal fade" id="grantAccessModal" tabindex="-1" aria-labelledby="grantAccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="grantAccessModalLabel">اعطای دسترسی به شجرنامه</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>عنوان شجرنامه: <span id="grant-family-tree-title"></span></h6>
                    <h6>توضیحات: <span id="grant-family-tree-description"></span></h6>
                    <h6>افراد در شجرنامه:</h6>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>نام</th>
                                <th>جنسیت</th>
                                <th>شهر محل تولد</th>
                                <th>شغل</th>
                            </tr>
                        </thead>
                        <tbody id="grant-family-tree-people">
                        </tbody>
                    </table>
                    <form id="grantAccessForm" method="POST">
                        @csrf
                        <input type="hidden" name="request_id" id="request_id">
                        <div class="mb-3">
                            <label for="user_id" class="form-label">کاربر:</label>
                            <select name="user_id" id="user_id" class="form-control" required>
                                <option value="">انتخاب کنید</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">اعطای دسترسی</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال مشاهده شجرنامه -->
    <div class="modal fade" id="viewFamilyTreeModal" tabindex="-1" aria-labelledby="viewFamilyTreeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewFamilyTreeModalLabel">مشاهده شجرنامه</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>عنوان شجرنامه: <span id="family-tree-title"></span></h6>
                    <h6>توضیحات: <span id="family-tree-description"></span></h6>
                    <h6>افراد در شجرنامه:</h6>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>نام</th>
                                <th>جنسیت</th>
                                <th>شهر محل تولد</th>
                                <th>شغل</th>
                            </tr>
                        </thead>
                        <tbody id="family-tree-people">
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال ویرایش فرد -->
    <div class="modal fade" id="editPersonModal" tabindex="-1" aria-labelledby="editPersonModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPersonModalLabel">ویرایش فرد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editPersonForm" method="POST">
                        @csrf
                        <input type="hidden" name="person_id" id="person_id">
                        <div class="mb-3">
                            <label for="person_name" class="form-label">نام:</label>
                            <input type="text" name="name" id="person_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="person_gender" class="form-label">جنسیت:</label>
                            <select name="gender" id="person_gender" class="form-control" required>
                                <option value="male">مرد</option>
                                <option value="female">زن</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="person_city_of_birth" class="form-label">شهر محل تولد:</label>
                            <select name="city_of_birth" id="person_city_of_birth" class="form-control">
                                <option value="">انتخاب کنید</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="person_birthday" class="form-label">تاریخ تولد:</label>
                            <input type="date" name="birthday" id="person_birthday" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="person_city_of_life" class="form-label">شهر محل زندگی:</label>
                            <select name="city_of_life" id="person_city_of_life" class="form-control">
                                <option value="">انتخاب کنید</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="person_occupation_id" class="form-label">شغل:</label>
                            <select name="occupation_id" id="person_occupation_id" class="form-control">
                                <option value="">انتخاب کنید</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="person_description" class="form-label">توضیحات:</label>
                            <textarea name="description" id="person_description" class="form-control"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">ذخیره</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال افزودن والد -->
    <div class="modal fade" id="addParentModal" tabindex="-1" aria-labelledby="addParentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addParentModalLabel">افزودن والد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addParentForm" method="POST">
                        @csrf
                        <input type="hidden" name="person_id" id="parent_person_id">
                        <input type="hidden" name="type" id="parent_type">
                        <div class="mb-3">
                            <label for="parent_name" class="form-label">نام:</label>
                            <input type="text" name="name" id="parent_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="parent_gender" class="form-label">جنسیت:</label>
                            <select name="gender" id="parent_gender" class="form-control" required>
                                <option value="male">مرد</option>
                                <option value="female">زن</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="parent_city_of_birth" class="form-label">شهر محل تولد:</label>
                            <select name="city_of_birth" id="parent_city_of_birth" class="form-control">
                                <option value="">انتخاب کنید</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="parent_birthday" class="form-label">تاریخ تولد:</label>
                            <input type="date" name="birthday" id="parent_birthday" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="parent_city_of_life" class="form-label">شهر محل زندگی:</label>
                            <select name="city_of_life" id="parent_city_of_life" class="form-control">
                                <option value="">انتخاب کنید</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="parent_occupation_id" class="form-label">شغل:</label>
                            <select name="occupation_id" id="parent_occupation_id" class="form-control">
                                <option value="">انتخاب کنید</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="parent_description" class="form-label">توضیحات:</label>
                            <textarea name="description" id="parent_description" class="form-control"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">ذخیره</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال افزودن فرزند -->
    <div class="modal fade" id="addChildModal" tabindex="-1" aria-labelledby="addChildModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addChildModalLabel">افزودن فرزند</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addChildForm" method="POST">
                        @csrf
                        <input type="hidden" name="person_id" id="child_person_id">
                        <div class="mb-3">
                            <label for="child_name" class="form-label">نام:</label>
                            <input type="text" name="name" id="child_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="child_gender" class="form-label">جنسیت:</label>
                            <select name="gender" id="child_gender" class="form-control" required>
                                <option value="male">مرد</option>
                                <option value="female">زن</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="child_city_of_birth" class="form-label">شهر محل تولد:</label>
                            <select name="city_of_birth" id="child_city_of_birth" class="form-control">
                                <option value="">انتخاب کنید</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="child_birthday" class="form-label">تاریخ تولد:</label>
                            <input type="date" name="birthday" id="child_birthday" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="child_city_of_life" class="form-label">شهر محل زندگی:</label>
                            <select name="city_of_life" id="child_city_of_life" class="form-control">
                                <option value="">انتخاب کنید</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="child_occupation_id" class="form-label">شغل:</label>
                            <select name="occupation_id" id="child_occupation_id" class="form-control">
                                <option value="">انتخاب کنید</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="child_description" class="form-label">توضیحات:</label>
                            <textarea name="description" id="child_description" class="form-control"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">ذخیره</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال افزودن همسر -->
    <div class="modal fade" id="addPartnerModal" tabindex="-1" aria-labelledby="addPartnerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPartnerModalLabel">افزودن همسر</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addPartnerForm" method="POST">
                        @csrf
                        <input type="hidden" name="person_id" id="partner_person_id">
                        <div class="mb-3">
                            <label for="partner_name" class="form-label">نام:</label>
                            <input type="text" name="name" id="partner_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="partner_gender" class="form-label">جنسیت:</label>
                            <select name="gender" id="partner_gender" class="form-control" required>
                                <option value="male">مرد</option>
                                <option value="female">زن</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="partner_city_of_birth" class="form-label">شهر محل تولد:</label>
                            <select name="city_of_birth" id="partner_city_of_birth" class="form-control">
                                <option value="">انتخاب کنید</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="partner_birthday" class="form-label">تاریخ تولد:</label>
                            <input type="date" name="partner_birthday" id="partner_birthday" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="partner_city_of_life" class="form-label">شهر محل زندگی:</label>
                            <select name="city_of_life" id="partner_city_of_life" class="form-control">
                                <option value="">انتخاب کنید</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="partner_occupation_id" class="form-label">شغل:</label>
                            <select name="occupation_id" id="partner_occupation_id" class="form-control">
                                <option value="">انتخاب کنید</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="partner_description" class="form-label">توضیحات:</label>
                            <textarea name="description" id="partner_description" class="form-control"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">ذخیره</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <a href="{{ route('admin.panel') }}" class="btn btn-secondary mt-3">بازگشت به پنل ادمین</a>

    @section('scripts')
        <script>
            // بارگذاری فرم ویرایش کاربر
            document.querySelectorAll('.edit-user').forEach(button => {
                button.addEventListener('click', function () {
                    const userId = this.getAttribute('data-id');
                    fetch(`{{ url('admin/users') }}/${userId}/edit`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                alert(data.error);
                                bootstrap.Modal.getInstance(document.querySelector('#editUserModal')).hide();
                                return;
                            }
                            document.getElementById('user_id').value = userId;
                            document.getElementById('name').value = data.user.name;
                            document.getElementById('email').value = data.user.email;
                            document.getElementById('is_admin').checked = data.user.is_admin;
                            document.getElementById('editUserForm').action = `{{ url('admin/users') }}/${userId}`;
                        });
                });
            });

            // بارگذاری فرم اعطای دسترسی
            document.querySelectorAll('.grant-access').forEach(button => {
                button.addEventListener('click', function () {
                    const requestId = this.getAttribute('data-id');
                    fetch(`{{ url('admin/family-tree-requests') }}/${requestId}/grant-access`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                alert(data.error);
                                bootstrap.Modal.getInstance(document.querySelector('#grantAccessModal')).hide();
                                return;
                            }
                            document.getElementById('request_id').value = requestId;
                            document.getElementById('grantAccessModalLabel').textContent = `اعطای دسترسی به شجرنامه: ${data.request.title}`;
                            document.getElementById('grant-family-tree-title').textContent = data.request.title;
                            document.getElementById('grant-family-tree-description').textContent = data.request.description || '-';
                            const peopleTable = document.getElementById('grant-family-tree-people');
                            peopleTable.innerHTML = '';
                            if (data.people.length === 0) {
                                peopleTable.innerHTML = '<tr><td colspan="4">هیچ فردی در این شجرنامه وجود ندارد.</td></tr>';
                            } else {
                                data.people.forEach(person => {
                                    const row = document.createElement('tr');
                                    row.innerHTML = `
                                        <td>${person.name}</td>
                                        <td>${person.gender === 'male' ? 'مرد' : 'زن'}</td>
                                        <td>${person.city_of_birth?.name || '-'}</td>
                                        <td>${person.occupation?.name || '-'}</td>
                                    `;
                                    peopleTable.appendChild(row);
                                });
                            }
                            const userSelect = document.getElementById('user_id');
                            userSelect.innerHTML = '<option value="">انتخاب کنید</option>';
                            data.users.forEach(user => {
                                const option = document.createElement('option');
                                option.value = user.id;
                                option.text = `${user.name} (${user.email})`;
                                userSelect.appendChild(option);
                            });
                            document.getElementById('grantAccessForm').action = `{{ url('admin/family-tree-requests') }}/${requestId}/grant-access`;
                        });
                });
            });

            // بارگذاری شجرنامه
            document.querySelectorAll('.view-family-tree').forEach(button => {
                button.addEventListener('click', function () {
                    const requestId = this.getAttribute('data-id');
                    fetch(`{{ url('admin/family-tree-requests') }}/${requestId}/show`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                alert(data.error);
                                bootstrap.Modal.getInstance(document.querySelector('#viewFamilyTreeModal')).hide();
                                return;
                            }
                            document.getElementById('family-tree-title').textContent = data.request.title;
                            document.getElementById('family-tree-description').textContent = data.request.description || '-';
                            const peopleTable = document.getElementById('family-tree-people');
                            peopleTable.innerHTML = '';
                            if (data.people.length === 0) {
                                peopleTable.innerHTML = '<tr><td colspan="4">هیچ فردی در این شجرنامه وجود ندارد.</td></tr>';
                            } else {
                                data.people.forEach(person => {
                                    const row = document.createElement('tr');
                                    row.innerHTML = `
                                        <td>${person.name}</td>
                                        <td>${person.gender === 'male' ? 'مرد' : 'زن'}</td>
                                        <td>${person.city_of_birth?.name || '-'}</td>
                                        <td>${person.occupation?.name || '-'}</td>
                                    `;
                                    peopleTable.appendChild(row);
                                });
                            }
                        });
                });
            });

            // بارگذاری فرم ویرایش فرد
            document.querySelectorAll('.edit-person').forEach(button => {
                button.addEventListener('click', function () {
                    const personId = this.getAttribute('data-id');
                    fetch(`{{ url('admin/people') }}/${personId}/edit`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                alert(data.error);
                                bootstrap.Modal.getInstance(document.querySelector('#editPersonModal')).hide();
                                return;
                            }
                            document.getElementById('person_id').value = personId;
                            document.getElementById('person_name').value = data.person.name;
                            document.getElementById('person_gender').value = data.person.gender;
                            document.getElementById('person_birthday').value = data.person.birthday || '';
                            document.getElementById('person_description').value = data.person.description || '';
                            const cityBirth = document.getElementById('person_city_of_birth');
                            const cityLife = document.getElementById('person_city_of_life');
                            const occupation = document.getElementById('person_occupation_id');
                            cityBirth.innerHTML = '<option value="">انتخاب کنید</option>';
                            cityLife.innerHTML = '<option value="">انتخاب کنید</option>';
                            occupation.innerHTML = '<option value="">انتخاب کنید</option>';
                            data.cities.forEach(city => {
                                const option1 = document.createElement('option');
                                const option2 = document.createElement('option');
                                option1.value = option2.value = city.id;
                                option1.text = option2.text = city.name;
                                if (city.id == data.person.city_of_birth) option1.selected = true;
                                if (city.id == data.person.city_of_life) option2.selected = true;
                                cityBirth.appendChild(option1);
                                cityLife.appendChild(option2);
                            });
                            data.occupations.forEach(occ => {
                                const option = document.createElement('option');
                                option.value = occ.id;
                                option.text = occ.name;
                                if (occ.id == data.person.occupation_id) option.selected = true;
                                occupation.appendChild(option);
                            });
                            document.getElementById('editPersonForm').action = `{{ url('admin/people') }}/${personId}`;
                        });
                });
            });

            // بارگذاری فرم افزودن والد
            document.querySelectorAll('.add-parent').forEach(button => {
                button.addEventListener('click', function () {
                    const personId = this.getAttribute('data-id');
                    const type = this.getAttribute('data-type');
                    fetch(`{{ url('admin/people') }}/${personId}/add-parent/${type}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                alert(data.error);
                                bootstrap.Modal.getInstance(document.querySelector('#addParentModal')).hide();
                                return;
                            }
                            document.getElementById('parent_person_id').value = personId;
                            document.getElementById('parent_type').value = type;
                            document.getElementById('addParentModalLabel').textContent = `افزودن ${type === 'father' ? 'پدر' : 'مادر'} برای ${data.person.name}`;
                            document.getElementById('parent_name').value = '';
                            document.getElementById('parent_gender').value = type === 'father' ? 'male' : 'female';
                            document.getElementById('parent_birthday').value = '';
                            document.getElementById('parent_description').value = '';
                            const cityBirth = document.getElementById('parent_city_of_birth');
                            const cityLife = document.getElementById('parent_city_of_life');
                            const occupation = document.getElementById('parent_occupation_id');
                            cityBirth.innerHTML = '<option value="">انتخاب کنید</option>';
                            cityLife.innerHTML = '<option value="">انتخاب کنید</option>';
                            occupation.innerHTML = '<option value="">انتخاب کنید</option>';
                            data.cities.forEach(city => {
                                const option1 = document.createElement('option');
                                const option2 = document.createElement('option');
                                option1.value = option2.value = city.id;
                                option1.text = option2.text = city.name;
                                cityBirth.appendChild(option1);
                                cityLife.appendChild(option2);
                            });
                            data.occupations.forEach(occ => {
                                const option = document.createElement('option');
                                option.value = occ.id;
                                option.text = occ.name;
                                occupation.appendChild(option);
                            });
                            document.getElementById('addParentForm').action = `{{ url('admin/people') }}/${personId}/add-parent/${type}`;
                        });
                });
            });

            // بارگذاری فرم افزودن فرزند
            document.querySelectorAll('.add-child').forEach(button => {
                button.addEventListener('click', function () {
                    const personId = this.getAttribute('data-id');
                    fetch(`{{ url('admin/people') }}/${personId}/add-child`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                alert(data.error);
                                bootstrap.Modal.getInstance(document.querySelector('#addChildModal')).hide();
                                return;
                            }
                            document.getElementById('child_person_id').value = personId;
                            document.getElementById('addChildModalLabel').textContent = `افزودن فرزند برای ${data.person.name}`;
                            document.getElementById('child_name').value = '';
                            document.getElementById('child_gender').value = '';
                            document.getElementById('child_birthday').value = '';
                            document.getElementById('child_description').value = '';
                            const cityBirth = document.getElementById('child_city_of_birth');
                            const cityLife = document.getElementById('child_city_of_life');
                            const occupation = document.getElementById('child_occupation_id');
                            cityBirth.innerHTML = '<option value="">انتخاب کنید</option>';
                            cityLife.innerHTML = '<option value="">انتخاب کنید</option>';
                            occupation.innerHTML = '<option value="">انتخاب کنید</option>';
                            data.cities.forEach(city => {
                                const option1 = document.createElement('option');
                                const option2 = document.createElement('option');
                                option1.value = option2.value = city.id;
                                option1.text = option2.text = city.name;
                                cityBirth.appendChild(option1);
                                cityLife.appendChild(option2);
                            });
                            data.occupations.forEach(occ => {
                                const option = document.createElement('option');
                                option.value = occ.id;
                                option.text = occ.name;
                                occupation.appendChild(option);
                            });
                            document.getElementById('addChildForm').action = `{{ url('admin/people') }}/${personId}/add-child`;
                        });
                });
            });

            // بارگذاری فرم افزودن همسر
            document.querySelectorAll('.add-partner').forEach(button => {
                button.addEventListener('click', function () {
                    const personId = this.getAttribute('data-id');
                    fetch(`{{ url('admin/people') }}/${personId}/add-partner`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                alert(data.error);
                                bootstrap.Modal.getInstance(document.querySelector('#addPartnerModal')).hide();
                                return;
                            }
                            document.getElementById('partner_person_id').value = personId;
                            document.getElementById('addPartnerModalLabel').textContent = `افزودن همسر برای ${data.person.name}`;
                            document.getElementById('partner_name').value = '';
                            document.getElementById('partner_gender').value = '';
                            document.getElementById('partner_birthday').value = '';
                            document.getElementById('partner_description').value = '';
                            const cityBirth = document.getElementById('partner_city_of_birth');
                            const cityLife = document.getElementById('partner_city_of_life');
                            const occupation = document.getElementById('partner_occupation_id');
                            cityBirth.innerHTML = '<option value="">انتخاب کنید</option>';
                            cityLife.innerHTML = '<option value="">انتخاب کنید</option>';
                            occupation.innerHTML = '<option value="">انتخاب کنید</option>';
                            data.cities.forEach(city => {
                                const option1 = document.createElement('option');
                                const option2 = document.createElement('option');
                                option1.value = option2.value = city.id;
                                option1.text = option2.text = city.name;
                                cityBirth.appendChild(option1);
                                cityLife.appendChild(option2);
                            });
                            data.occupations.forEach(occ => {
                                const option = document.createElement('option');
                                option.value = occ.id;
                                option.text = occ.name;
                                occupation.appendChild(option);
                            });
                            document.getElementById('addPartnerForm').action = `{{ url('admin/people') }}/${personId}/add-partner`;
                        });
                });
            });
        </script>
    @endsection
@endsection