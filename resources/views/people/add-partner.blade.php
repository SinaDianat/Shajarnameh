@extends('layouts.app')

@section('title', 'افزودن همسر')

@section('content')
    <div class="container my-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">افزودن همسر برای {{ $person->name }}</h5>
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

                <form action="{{ route('people.store-partner', $person->id) }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">نام:</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="gender" class="form-label">جنسیت:</label>
                            <select name="gender" id="gender" class="form-control" required>
                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>مرد</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>زن</option>
                            </select>
                            @error('gender')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="city_of_birth_input" class="form-label">شهر محل تولد:</label>
                            <input type="text" id="city_of_birth_input" class="form-control" placeholder="جستجوی شهر..." value="{{ old('city_of_birth_input') }}" autocomplete="off">
                            <input type="hidden" name="city_of_birth" id="city_of_birth" value="{{ old('city_of_birth') }}">
                            <div id="city_of_birth_suggestions" class="dropdown-menu w-100"></div>
                            @error('city_of_birth')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="birthday" class="form-label">تاریخ تولد:</label>
                            <input type="date" name="birthday" id="birthday" class="form-control" value="{{ old('birthday') }}">
                            @error('birthday')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="city_of_life_input" class="form-label">شهر محل زندگی:</label>
                            <input type="text" id="city_of_life_input" class="form-control" placeholder="جستجوی شهر..." value="{{ old('city_of_life_input') }}" autocomplete="off">
                            <input type="hidden" name="city_of_life" id="city_of_life" value="{{ old('city_of_life') }}">
                            <div id="city_of_life_suggestions" class="dropdown-menu w-100"></div>
                            @error('city_of_life')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="occupation_id" class="form-label">شغل:</label>
                            <select name="occupation_id" id="occupation_id" class="form-control">
                                <option value="">انتخاب کنید</option>
                                @foreach ($occupations as $occupation)
                                    <option value="{{ $occupation->id }}" {{ old('occupation_id') == $occupation->id ? 'selected' : '' }}>{{ $occupation->name }}</option>
                                @endforeach
                            </select>
                            @error('occupation_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 mb-3">
                            <label for="description" class="form-label">توضیحات:</label>
                            <textarea name="description" id="description" class="form-control">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">ذخیره</button>
                        <a href="{{ route('people.family_tree') }}" class="btn btn-secondary">بازگشت به شجره‌نامه</a>
                        <a href="{{ route('user.panel') }}" class="btn btn-secondary">بازگشت به پنل کاربری</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @section('scripts')
        <script>
            function setupCityAutocomplete(inputId, hiddenInputId, suggestionsId) {
                const input = document.getElementById(inputId);
                const hiddenInput = document.getElementById(hiddenInputId);
                const suggestions = document.getElementById(suggestionsId);

                input.addEventListener('input', async function () {
                    const query = this.value;
                    if (query.length < 2) {
                        suggestions.innerHTML = '';
                        suggestions.classList.remove('show');
                        return;
                    }

                    try {
                        const response = await fetch(`{{ route('api.cities.search') }}?q=${encodeURIComponent(query)}`);
                        if (!response.ok) {
                            console.error('HTTP Error:', response.status, response.statusText);
                            suggestions.innerHTML = '<div class="dropdown-item text-danger">خطا در دریافت داده‌ها: ' + response.status + '</div>';
                            suggestions.classList.add('show');
                            return;
                        }
                        const cities = await response.json();
                        suggestions.innerHTML = '';
                        if (cities.length === 0) {
                            suggestions.innerHTML = '<div class="dropdown-item">هیچ شهری یافت نشد</div>';
                        } else {
                            cities.forEach(city => {
                                const item = document.createElement('div');
                                item.classList.add('dropdown-item');
                                item.textContent = `${city.name} (${city.country})`;
                                item.addEventListener('click', () => {
                                    input.value = `${city.name} (${city.country})`;
                                    hiddenInput.value = city.id;
                                    suggestions.innerHTML = '';
                                    suggestions.classList.remove('show');
                                });
                                suggestions.appendChild(item);
                            });
                        }
                        suggestions.classList.add('show');
                    } catch (error) {
                        console.error('Error fetching cities:', error);
                        suggestions.innerHTML = '<div class="dropdown-item text-danger">خطایی رخ داد: ' + error.message + '</div>';
                        suggestions.classList.add('show');
                    }
                });

                document.addEventListener('click', (e) => {
                    if (!input.contains(e.target) && !suggestions.contains(e.target)) {
                        suggestions.classList.remove('show');
                    }
                });
            }

            setupCityAutocomplete('city_of_birth_input', 'city_of_birth', 'city_of_birth_suggestions');
            setupCityAutocomplete('city_of_life_input', 'city_of_life', 'city_of_life_suggestions');
        </script>
    @endsection
@endsection