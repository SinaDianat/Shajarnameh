@extends('layouts.app')

@section('title', 'جزئیات فرد')

@section('content')
    <div class="container my-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">جزئیات فرد: {{ $person->name }}</h5>
                <a href="{{ route('people.family_tree') }}" class="btn btn-light btn-sm">بازگشت به شجره‌نامه</a>
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

                <div class="row g-4">
                    <!-- کارت مشخصات -->
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0">مشخصات</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item"><strong>نام:</strong> {{ $person->name }}</li>
                                    <li class="list-group-item"><strong>جنسیت:</strong> {{ $person->gender == 'male' ? 'مرد' : 'زن' }}</li>
                                    <li class="list-group-item"><strong>شهر محل تولد:</strong> {{ $person->cityOfBirth?->name ?? '-' }} ({{ $person->cityOfBirth?->country?->name ?? '-' }})</li>
                                    <li class="list-group-item"><strong>تاریخ تولد:</strong> {{ $person->birthday ? Jalalian::fromCarbon(\Carbon\Carbon::parse($person->birthday))->format('Y/m/d') : '-' }}</li>
                                    <li class="list-group-item"><strong>شهر محل زندگی:</strong> {{ $person->cityOfLife?->name ?? '-' }} ({{ $person->cityOfLife?->country?->name ?? '-' }})</li>
                                    <li class="list-group-item"><strong>شغل:</strong> {{ $person->occupation?->name ?? '-' }}</li>
                                    <li class="list-group-item"><strong>توضیحات:</strong> {{ $person->description ?? '-' }}</li>
                                    @if ($person->date_of_die)
                                        <li class="list-group-item"><strong>تاریخ فوت:</strong> {{ $person->date_of_die ? Jalalian::fromCarbon(\Carbon\Carbon::parse($person->date_of_die))->format('Y/m/d') : '-' }}</li>
                                        <li class="list-group-item"><strong>شهر محل فوت:</strong> {{ $person->cityOfDie?->name ?? '-' }} ({{ $person->cityOfDie?->country?->name ?? '-' }})</li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- کارت روابط -->
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0">روابط</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <strong>پدر:</strong>
                                        @if ($person->father)
                                            <a href="{{ route('people.show', $person->father_id) }}">{{ $person->father->name }}</a>
                                        @else
                                            -
                                        @endif
                                    </li>
                                    <li class="list-group-item">
                                        <strong>مادر:</strong>
                                        @if ($person->mother)
                                            <a href="{{ route('people.show', $person->mother_id) }}">{{ $person->mother->name }}</a>
                                        @else
                                            -
                                        @endif
                                    </li>
                                    <li class="list-group-item">
                                        <strong>فرزندان:</strong>
                                        @if ($person->children->isNotEmpty())
                                            @foreach ($person->children as $child)
                                                <div><a href="{{ route('people.show', $child->id) }}">{{ $child->name }}</a></div>
                                            @endforeach
                                        @else
                                            -
                                        @endif
                                    </li>
                                    <li class="list-group-item">
                                        <strong>همسر:</strong>
                                        @if ($person->partners->isNotEmpty())
                                            @foreach ($person->partners as $partner)
                                                <div><a href="{{ route('people.show', $partner->id) }}">{{ $partner->name }}</a></div>
                                            @endforeach
                                        @else
                                            -
                                        @endif
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('people.edit', $person->id) }}" class="btn btn-primary">ویرایش فرد</a>
                    <a href="{{ route('people.family_tree') }}" class="btn btn-secondary">بازگشت به شجره‌نامه</a>
                    <a href="{{ route('user.panel') }}" class="btn btn-secondary">بازگشت به پنل کاربری</a>
                </div>
            </div>
        </div>
    </div>
@endsection
