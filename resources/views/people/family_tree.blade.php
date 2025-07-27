@extends('layouts.app')

@section('title', 'شجره‌نامه')

@section('content')
    <div class="container my-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">شجره‌نامه شما</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('people.create') }}" class="btn btn-light btn-sm">افزودن فرد جدید</a>
                    <a href="{{ route('user.panel') }}" class="btn btn-light btn-sm">بازگشت به پنل کاربری</a>
                </div>
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

                @if ($people->isEmpty())
                    <p class="text-muted">هیچ فردی در شجره‌نامه شما وجود ندارد.</p>
                @else
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        @foreach ($people as $person)
                            <div class="col">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">{{ $person->name }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text"><strong>جنسیت:</strong> {{ $person->gender == 'male' ? 'مرد' : 'زن' }}</p>
                                        <p class="card-text"><strong>شهر محل تولد:</strong> {{ $person->cityOfBirth ? $person->cityOfBirth->name . ' (' . ($person->cityOfBirth->country->name ?? '-') . ')' : '-' }}</p>
                                        <p class="card-text"><strong>شهر محل زندگی:</strong> {{ $person->cityOfLife ? $person->cityOfLife->name . ' (' . ($person->cityOfLife->country->name ?? '-') . ')' : '-' }}</p>
                                        <p class="card-text"><strong>شغل:</strong> {{ $person->occupation?->name ?? '-' }}</p>
                                        <p class="card-text"><strong>پدر:</strong>
                                            @if ($person->father)
                                                <a href="{{ route('people.show', $person->father_id) }}">{{ $person->father->name }}</a>
                                            @else
                                                -
                                            @endif
                                        </p>
                                        <p class="card-text"><strong>سن:</strong> {{ $person->birthday ? $this->calculateAge($person->birthday) : '-' }} سال</p>
                                        @if ($person->date_of_die)
                                            <p class="card-text"><strong>تاریخ فوت:</strong> {{ $person->date_of_die ? \Jalalian::fromCarbon(\Carbon\Carbon::parse($person->date_of_die))->format('Y/m/d') : '-' }}</p>
                                            <p class="card-text"><strong>شهر محل فوت:</strong> {{ $person->cityOfDie ? $person->cityOfDie->name . ' (' . ($person->cityOfDie->country->name ?? '-') . ')' : '-' }}</p>
                                        @endif
                                    </div>
                                    <div class="card-footer d-flex flex-wrap gap-2">
                                        <a href="{{ route('people.show', $person->id) }}" class="btn btn-sm btn-info">مشاهده جزئیات</a>
                                        <a href="{{ route('people.edit', $person->id) }}" class="btn btn-sm btn-primary">ویرایش</a>
                                        @if ($person->father_id)
                                            <a href="{{ route('people.edit', $person->father_id) }}" class="btn btn-sm btn-primary">ویرایش پدر</a>
                                        @else
                                            <a href="{{ route('people.add-parent', [$person->id, 'father']) }}" class="btn btn-sm btn-primary">افزودن پدر</a>
                                        @endif
                                        @if ($person->mother_id)
                                            <a href="{{ route('people.edit', $person->mother_id) }}" class="btn btn-sm btn-primary">ویرایش مادر</a>
                                        @else
                                            <a href="{{ route('people.add-parent', [$person->id, 'mother']) }}" class="btn btn-sm btn-primary">افزودن مادر</a>
                                        @endif
                                        <a href="{{ route('people.add-child', $person->id) }}" class="btn btn-sm btn-primary">افزودن فرزند</a>
                                        <a href="{{ route('people.add-partner', $person->id) }}" class="btn btn-sm btn-primary">افزودن همسر</a>
                                        <form action="{{ route('people.destroy', $person->id) }}" method="POST" style="display:inline;"
                                              onsubmit="return confirm('آیا مطمئن هستید که می‌خواهید این فرد را حذف کنید؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function calculateAge(birthday) {
            if (!birthday) return 0;
            const birthDate = new Date(birthday);
            const today = new Date('2025-07-26'); // تاریخ فعلی
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            return age;
        }
    </script>
@endsection