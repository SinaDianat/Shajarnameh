<?php

namespace App\Http\Controllers;

use App\Models\People;
use App\Models\City;
use App\Models\Occupation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PeopleController extends Controller
{
    /**
     * نمایش لیست افراد در شجره‌نامه
     */
    public function index()
    {
        $user = Auth::user();
        $people = People::whereIn('id', $user->access_ids ?? [])
                        ->with(['cityOfBirth', 'cityOfBirth.country', 'cityOfLife', 'cityOfLife.country', 'cityOfDie', 'cityOfDie.country', 'occupation', 'father', 'mother', 'children', 'partners'])
                        ->get();
        return view('people.family_tree', compact('people'));
    }

    /**
     * نمایش فرم افزودن فرد جدید
     */
    public function create()
    {
        $occupations = Occupation::all();
        return view('people.create', compact('occupations'));
    }

    /**
     * ذخیره فرد جدید
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'gender' => 'required|in:male,female',
                'city_of_birth' => 'nullable|exists:cities,id',
                'birthday' => 'nullable|date',
                'city_of_life' => 'nullable|exists:cities,id',
                'occupation_id' => 'nullable|exists:occupations,id',
                'new_occupation' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'city_of_die' => 'nullable|exists:cities,id',
                'date_of_die' => 'nullable|date|after_or_equal:birthday',
                'is_deceased' => 'nullable|boolean',
            ]);

            $occupationId = $this->handleOccupation($request->input('occupation_id'), $request->input('new_occupation'));
            $validated['occupation_id'] = $occupationId;

            // فقط اگر فوت شده باشد، فیلدهای فوت را ذخیره کن
            if (!$request->has('is_deceased')) {
                $validated['city_of_die'] = null;
                $validated['date_of_die'] = null;
            }

            $person = People::create($validated);
            $user->access_ids = array_merge($user->access_ids ?? [], [$person->id]);
            $user->save();

            DB::commit();
            return redirect()->route('people.family_tree')->with('success', 'فرد با موفقیت به شجره‌نامه اضافه شد.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing person: ' . $e->getMessage());
            return redirect()->back()->with('error', 'خطایی در افزودن فرد رخ داد: ' . $e->getMessage());
        }
    }

    /**
     * نمایش اطلاعات فرد
     */
    public function show($id)
    {
        $person = People::with(['cityOfBirth', 'cityOfBirth.country', 'cityOfLife', 'cityOfLife.country', 'cityOfDie', 'cityOfDie.country', 'occupation', 'father', 'mother', 'children', 'partners'])
                        ->findOrFail($id);
        if (!in_array($id, Auth::user()->access_ids ?? [])) {
            return redirect()->route('people.family_tree')->with('error', 'شما به این فرد دسترسی ندارید.');
        }
        return view('people.show', compact('person'));
    }

    /**
     * نمایش فرم ویرایش فرد
     */
    public function edit($id)
    {
        $person = People::findOrFail($id);
        if (!in_array($id, Auth::user()->access_ids ?? [])) {
            return redirect()->route('people.family_tree')->with('error', 'شما به این فرد دسترسی ندارید.');
        }
        $occupations = Occupation::all();
        return view('people.edit', compact('person', 'occupations'));
    }

    /**
     * به‌روزرسانی اطلاعات فرد
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $person = People::findOrFail($id);
            if (!in_array($id, Auth::user()->access_ids ?? [])) {
                return redirect()->route('people.family_tree')->with('error', 'شما به این فرد دسترسی ندارید.');
            }
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'gender' => 'required|in:male,female',
                'city_of_birth' => 'nullable|exists:cities,id',
                'birthday' => 'nullable|date',
                'city_of_life' => 'nullable|exists:cities,id',
                'occupation_id' => 'nullable|exists:occupations,id',
                'new_occupation' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'city_of_die' => 'nullable|exists:cities,id',
                'date_of_die' => 'nullable|date|after_or_equal:birthday',
                'is_deceased' => 'nullable|boolean',
            ]);

            $occupationId = $this->handleOccupation($request->input('occupation_id'), $request->input('new_occupation'));
            $validated['occupation_id'] = $occupationId;

            // فقط اگر فوت شده باشد، فیلدهای فوت را ذخیره کن
            if (!$request->has('is_deceased')) {
                $validated['city_of_die'] = null;
                $validated['date_of_die'] = null;
            }

            $person->update($validated);
            DB::commit();
            return redirect()->route('people.family_tree')->with('success', 'فرد با موفقیت ویرایش شد.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating person: ' . $e->getMessage());
            return redirect()->back()->with('error', 'خطایی در ویرایش فرد رخ داد: ' . $e->getMessage());
        }
    }

    /**
     * مدیریت شغل (اضافه کردن شغل جدید یا استفاده از موجود)
     */
    protected function handleOccupation($occupationId, $newOccupation)
    {
        if ($newOccupation && !$occupationId) {
            $occupation = Occupation::firstOrCreate(['name' => trim($newOccupation)]);
            return $occupation->id;
        }
        return $occupationId;
    }

    /**
     * نمایش فرم افزودن والد (پدر یا مادر)
     */
    public function addParent($id, $type)
    {
        $person = People::findOrFail($id);
        if (!in_array($id, Auth::user()->access_ids ?? [])) {
            return redirect()->route('people.family_tree')->with('error', 'شما به این فرد دسترسی ندارید.');
        }
        if (!in_array($type, ['father', 'mother'])) {
            return redirect()->route('people.family_tree')->with('error', 'نوع والد نامعتبر است.');
        }
        if (($type === 'father' && $person->father_id) || ($type === 'mother' && $person->mother_id)) {
            return redirect()->route('people.family_tree')->with('error', $type === 'father' ? 'پدر قبلاً اضافه شده است.' : 'مادر قبلاً اضافه شده است.');
        }
        $occupations = Occupation::all();
        return view('people.add-parent', compact('person', 'type', 'occupations'));
    }

    /**
     * ذخیره والد جدید
     */
    public function storeParent(Request $request, $id, $type)
    {
        DB::beginTransaction();
        try {
            $person = People::findOrFail($id);
            if (!in_array($id, Auth::user()->access_ids ?? [])) {
                return redirect()->route('people.family_tree')->with('error', 'شما به این فرد دسترسی ندارید.');
            }
            if (!in_array($type, ['father', 'mother'])) {
                return redirect()->route('people.family_tree')->with('error', 'نوع والد نامعتبر است.');
            }
            if (($type === 'father' && $person->father_id) || ($type === 'mother' && $person->mother_id)) {
                return redirect()->route('people.family_tree')->with('error', $type === 'father' ? 'پدر قبلاً اضافه شده است.' : 'مادر قبلاً اضافه شده است.');
            }
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'gender' => 'required|in:male,female',
                'city_of_birth' => 'nullable|exists:cities,id',
                'birthday' => 'nullable|date',
                'city_of_life' => 'nullable|exists:cities,id',
                'occupation_id' => 'nullable|exists:occupations,id',
                'description' => 'nullable|string',
            ]);

            $parent = People::create($validated);
            $user = Auth::user();
            $user->access_ids = array_merge($user->access_ids ?? [], [$parent->id]);
            $user->save();

            if ($type === 'father') {
                $person->father_id = $parent->id;
            } elseif ($type === 'mother') {
                $person->mother_id = $parent->id;
            }
            $person->save();

            $parent->children_ids = array_merge($parent->children_ids ?? [], [$person->id]);
            $parent->save();

            DB::commit();
            return redirect()->route('people.family_tree')->with('success', 'والد با موفقیت اضافه شد.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing parent: ' . $e->getMessage());
            return redirect()->back()->with('error', 'خطایی در افزودن والد رخ داد: ' . $e->getMessage());
        }
    }

    /**
     * نمایش فرم افزودن فرزند
     */
    public function addChild($id)
    {
        $person = People::findOrFail($id);
        if (!in_array($id, Auth::user()->access_ids ?? [])) {
            return redirect()->route('people.family_tree')->with('error', 'شما به این فرد دسترسی ندارید.');
        }
        $occupations = Occupation::all();
        return view('people.add-child', compact('person', 'occupations'));
    }

    /**
     * ذخیره فرزند جدید
     */
    public function storeChild(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $person = People::findOrFail($id);
            if (!in_array($id, Auth::user()->access_ids ?? [])) {
                return redirect()->route('people.family_tree')->with('error', 'شما به این فرد دسترسی ندارید.');
            }
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'gender' => 'required|in:male,female',
                'city_of_birth' => 'nullable|exists:cities,id',
                'birthday' => 'nullable|date',
                'city_of_life' => 'nullable|exists:cities,id',
                'occupation_id' => 'nullable|exists:occupations,id',
                'description' => 'nullable|string',
            ]);

            $child = People::create($validated);
            $user = Auth::user();
            $user->access_ids = array_merge($user->access_ids ?? [], [$child->id]);
            $user->save();

            $person->children_ids = array_merge($person->children_ids ?? [], [$child->id]);
            $person->save();

            if ($person->gender === 'male') {
                $child->father_id = $person->id;
            } elseif ($person->gender === 'female') {
                $child->mother_id = $person->id;
            }
            $child->save();

            DB::commit();
            return redirect()->route('people.family_tree')->with('success', 'فرزند با موفقیت اضافه شد.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing child: ' . $e->getMessage());
            return redirect()->back()->with('error', 'خطایی در افزودن فرزند رخ داد: ' . $e->getMessage());
        }
    }

    /**
     * نمایش فرم افزودن همسر
     */
    public function addPartner($id)
    {
        $person = People::findOrFail($id);
        if (!in_array($id, Auth::user()->access_ids ?? [])) {
            return redirect()->route('people.family_tree')->with('error', 'شما به این فرد دسترسی ندارید.');
        }
        $occupations = Occupation::all();
        return view('people.add-partner', compact('person', 'occupations'));
    }

    /**
     * ذخیره همسر جدید
     */
    public function storePartner(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $person = People::findOrFail($id);
            if (!in_array($id, Auth::user()->access_ids ?? [])) {
                return redirect()->route('people.family_tree')->with('error', 'شما به این فرد دسترسی ندارید.');
            }
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'gender' => 'required|in:male,female',
                'city_of_birth' => 'nullable|exists:cities,id',
                'birthday' => 'nullable|date',
                'city_of_life' => 'nullable|exists:cities,id',
                'occupation_id' => 'nullable|exists:occupations,id',
                'description' => 'nullable|string',
            ]);

            $partner = People::create($validated);
            $user = Auth::user();
            $user->access_ids = array_merge($user->access_ids ?? [], [$partner->id]);
            $user->save();

            $person->partners_ids = array_merge($person->partners_ids ?? [], [$partner->id]);
            $person->save();

            $partner->partners_ids = array_merge($partner->partners_ids ?? [], [$person->id]);
            $partner->save();

            DB::commit();
            return redirect()->route('people.family_tree')->with('success', 'همسر با موفقیت اضافه شد.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing partner: ' . $e->getMessage());
            return redirect()->back()->with('error', 'خطایی در افزودن همسر رخ داد: ' . $e->getMessage());
        }
    }

    /**
     * حذف فرد از شجره‌نامه
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $person = People::findOrFail($id);
            if (!in_array($id, Auth::user()->access_ids ?? [])) {
                return redirect()->route('people.family_tree')->with('error', 'شما به این فرد دسترسی ندارید.');
            }

            // بررسی وجود فرزند
            if ($person->children->isNotEmpty()) {
                return redirect()->route('people.family_tree')->with('error', 'نمی‌توانید فردی که فرزند دارد را حذف کنید.');
            }

            // به‌روزرسانی روابط وابسته (مثلاً اگر پدر یا مادر دیگری باشد)
            People::where('father_id', $id)->update(['father_id' => null]);
            People::where('mother_id', $id)->update(['mother_id' => null]);
            People::whereIn('id', $person->partners_ids ?? [])->update(['partners_ids' => DB::raw("array_remove(partners_ids, '$id')")]);

            // حذف از access_ids کاربر
            $user = Auth::user();
            $user->access_ids = array_filter($user->access_ids ?? [], fn($accessId) => $accessId != $id);
            $user->save();

            // حذف فرد
            $person->delete();

            DB::commit();
            return redirect()->route('people.family_tree')->with('success', 'فرد با موفقیت حذف شد.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting person: ' . $e->getMessage());
            return redirect()->route('people.family_tree')->with('error', 'خطایی در حذف فرد رخ داد: ' . $e->getMessage());
        }
    }
}