<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\FamilyTreeRequest;
use App\Models\People;
use App\Models\City;
use App\Models\Occupation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        if (!Auth::user()->is_admin) {
            return redirect()->route('user.panel')->with('error', 'فقط ادمین‌ها می‌توانند به پنل ادمین دسترسی داشته باشند.');
        }

        $users = User::all();
        $requests = FamilyTreeRequest::with('user')->get();
        $approvedRequests = FamilyTreeRequest::where('status', 'approved')->with('user')->get();
        return view('admin.admin-panel', compact('users', 'requests', 'approvedRequests'));
    }

    public function edit($id)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'فقط ادمین‌ها می‌توانند کاربران را ویرایش کنند.']);
        }

        $user = User::findOrFail($id);
        return response()->json(['user' => $user]);
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->is_admin) {
            return redirect()->route('user.panel')->with('error', 'فقط ادمین‌ها می‌توانند کاربران را ویرایش کنند.');
        }

        $user = User::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'is_admin' => 'boolean',
        ]);

        $user->update($validated);
        return redirect()->route('admin.panel')->with('success', 'کاربر با موفقیت ویرایش شد.');
    }

    public function destroy($id)
    {
        if (!Auth::user()->is_admin) {
            return redirect()->route('user.panel')->with('error', 'فقط ادمین‌ها می‌توانند کاربران را حذف کنند.');
        }

        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('admin.panel')->with('success', 'کاربر با موفقیت حذف شد.');
    }

    public function toggleAdmin($id)
    {
        if (!Auth::user()->is_admin) {
            return redirect()->route('user.panel')->with('error', 'فقط ادمین‌ها می‌توانند وضعیت ادمین را تغییر دهند.');
        }

        $user = User::findOrFail($id);
        $user->is_admin = !$user->is_admin;
        $user->save();
        return redirect()->route('admin.panel')->with('success', 'وضعیت ادمین کاربر با موفقیت تغییر کرد.');
    }

    public function showFamilyTree($requestId)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'فقط ادمین‌ها می‌توانند شجره‌نامه را مشاهده کنند.']);
        }

        $request = FamilyTreeRequest::findOrFail($requestId);
        $people = People::whereIn('id', $request->user->access_ids ?? [])->with(['cityOfBirth', 'occupation'])->get();
        return response()->json(['request' => $request, 'people' => $people]);
    }

    public function grantAccessForm($requestId)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'فقط ادمین‌ها می‌توانند دسترسی اعطا کنند.']);
        }

        $request = FamilyTreeRequest::findOrFail($requestId);
        $users = User::all();
        $people = People::whereIn('id', $request->user->access_ids ?? [])->with(['cityOfBirth', 'occupation'])->get();
        return response()->json(['request' => $request, 'users' => $users, 'people' => $people]);
    }

    public function grantAccess(Request $request, $requestId)
    {
        if (!Auth::user()->is_admin) {
            return redirect()->route('user.panel')->with('error', 'فقط ادمین‌ها می‌توانند دسترسی اعطا کنند.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $familyTreeRequest = FamilyTreeRequest::findOrFail($requestId);
        $user = User::findOrFail($validated['user_id']);
        $accessIds = $user->access_ids ?? [];
        $people = People::whereIn('id', $familyTreeRequest->user->access_ids ?? [])->pluck('id')->toArray();
        $user->access_ids = array_unique(array_merge($accessIds, $people));
        $user->save();

        return redirect()->route('admin.panel')->with('success', 'دسترسی با موفقیت اعطا شد.');
    }

    public function editPerson($id)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'فقط ادمین‌ها می‌توانند افراد را ویرایش کنند.']);
        }

        $person = People::findOrFail($id);
        $cities = City::all();
        $occupations = Occupation::all();
        return response()->json(['person' => $person, 'cities' => $cities, 'occupations' => $occupations]);
    }

    public function updatePerson(Request $request, $id)
    {
        if (!Auth::user()->is_admin) {
            return redirect()->route('admin.panel')->with('error', 'فقط ادمین‌ها می‌توانند افراد را ویرایش کنند.');
        }

        $person = People::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'city_of_birth' => 'nullable|exists:cities,id',
            'birthday' => 'nullable|date',
            'city_of_life' => 'nullable|exists:cities,id',
            'occupation_id' => 'nullable|exists:occupations,id',
            'description' => 'nullable|string',
        ]);

        $person->update($validated);
        return redirect()->route('admin.panel')->with('success', 'اطلاعات فرد با موفقیت به‌روزرسانی شد.');
    }

    public function destroyPerson($id)
    {
        if (!Auth::user()->is_admin) {
            return redirect()->route('admin.panel')->with('error', 'فقط ادمین‌ها می‌توانند افراد را حذف کنند.');
        }

        $person = People::findOrFail($id);

        // به‌روزرسانی روابط
        if ($person->father_id) {
            $father = People::find($person->father_id);
            if ($father) {
                $father->children_ids = array_diff($father->children_ids ?? [], [$person->id]);
                $father->save();
            }
        }
        if ($person->mother_id) {
            $mother = People::find($person->mother_id);
            if ($mother) {
                $mother->children_ids = array_diff($mother->children_ids ?? [], [$person->id]);
                $mother->save();
            }
        }
        foreach ($person->children_ids ?? [] as $childId) {
            $child = People::find($childId);
            if ($child) {
                if ($child->father_id == $person->id) {
                    $child->father_id = null;
                }
                if ($child->mother_id == $person->id) {
                    $child->mother_id = null;
                }
                $child->save();
            }
        }
        foreach ($person->partners_ids ?? [] as $partnerId) {
            $partner = People::find($partnerId);
            if ($partner) {
                $partner->partners_ids = array_diff($partner->partners_ids ?? [], [$person->id]);
                $partner->save();
            }
        }

        // حذف فرد از access_ids کاربران
        $users = User::whereJsonContains('access_ids', $person->id)->get();
        foreach ($users as $user) {
            $user->access_ids = array_diff($user->access_ids ?? [], [$person->id]);
            $user->save();
        }

        $person->delete();
        return redirect()->route('admin.panel')->with('success', 'فرد با موفقیت حذف شد.');
    }

    public function addParent($id, $type)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'فقط ادمین‌ها می‌توانند والدین اضافه کنند.']);
        }

        $person = People::findOrFail($id);
        $cities = City::all();
        $occupations = Occupation::all();
        return response()->json([
            'person' => $person,
            'type' => $type,
            'cities' => $cities,
            'occupations' => $occupations
        ]);
    }

    public function storeParent(Request $request, $id, $type)
    {
        if (!Auth::user()->is_admin) {
            return redirect()->route('admin.panel')->with('error', 'فقط ادمین‌ها می‌توانند والدین اضافه کنند.');
        }

        $person = People::findOrFail($id);
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
        if ($type === 'father') {
            $person->father_id = $parent->id;
        } elseif ($type === 'mother') {
            $person->mother_id = $parent->id;
        }
        $parent->children_ids = array_merge($parent->children_ids ?? [], [$person->id]);
        $parent->save();
        $person->save();

        // اضافه کردن به access_ids کاربران مرتبط
        $users = User::whereJsonContains('access_ids', $person->id)->get();
        foreach ($users as $user) {
            $user->access_ids = array_unique(array_merge($user->access_ids ?? [], [$parent->id]));
            $user->save();
        }

        return redirect()->route('admin.panel')->with('success', 'والد با موفقیت اضافه شد.');
    }

    public function addChild($id)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'فقط ادمین‌ها می‌توانند فرزند اضافه کنند.']);
        }

        $person = People::findOrFail($id);
        $cities = City::all();
        $occupations = Occupation::all();
        return response()->json([
            'person' => $person,
            'cities' => $cities,
            'occupations' => $occupations
        ]);
    }

    public function storeChild(Request $request, $id)
    {
        if (!Auth::user()->is_admin) {
            return redirect()->route('admin.panel')->with('error', 'فقط ادمین‌ها می‌توانند فرزند اضافه کنند.');
        }

        $person = People::findOrFail($id);
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
        if ($person->gender === 'male') {
            $child->father_id = $person->id;
        } elseif ($person->gender === 'female') {
            $child->mother_id = $person->id;
        }
        $person->children_ids = array_merge($person->children_ids ?? [], [$child->id]);
        $person->save();
        $child->save();

        // اضافه کردن به access_ids کاربران مرتبط
        $users = User::whereJsonContains('access_ids', $person->id)->get();
        foreach ($users as $user) {
            $user->access_ids = array_unique(array_merge($user->access_ids ?? [], [$child->id]));
            $user->save();
        }

        return redirect()->route('admin.panel')->with('success', 'فرزند با موفقیت اضافه شد.');
    }

    public function addPartner($id)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'فقط ادمین‌ها می‌توانند همسر اضافه کنند.']);
        }

        $person = People::findOrFail($id);
        $cities = City::all();
        $occupations = Occupation::all();
        return response()->json([
            'person' => $person,
            'cities' => $cities,
            'occupations' => $occupations
        ]);
    }

    public function storePartner(Request $request, $id)
    {
        if (!Auth::user()->is_admin) {
            return redirect()->route('admin.panel')->with('error', 'فقط ادمین‌ها می‌توانند همسر اضافه کنند.');
        }

        $person = People::findOrFail($id);
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
        $person->partners_ids = array_merge($person->partners_ids ?? [], [$partner->id]);
        $partner->partners_ids = array_merge($partner->partners_ids ?? [], [$person->id]);
        $person->save();
        $partner->save();

        // اضافه کردن به access_ids کاربران مرتبط
        $users = User::whereJsonContains('access_ids', $person->id)->get();
        foreach ($users as $user) {
            $user->access_ids = array_unique(array_merge($user->access_ids ?? [], [$partner->id]));
            $user->save();
        }

        return redirect()->route('admin.panel')->with('success', 'همسر با موفقیت اضافه شد.');
    }

    public function destroyFamilyTree($requestId)
    {
        if (!Auth::user()->is_admin) {
            return redirect()->route('admin.panel')->with('error', 'فقط ادمین‌ها می‌توانند شجره‌نامه را حذف کنند.');
        }

        $familyTreeRequest = FamilyTreeRequest::findOrFail($requestId);
        $peopleIds = People::whereIn('id', $familyTreeRequest->user->access_ids ?? [])->pluck('id')->toArray();

        // حذف افراد مرتبط
        foreach ($peopleIds as $personId) {
            $person = People::find($personId);
            if ($person) {
                if ($person->father_id) {
                    $father = People::find($person->father_id);
                    if ($father) {
                        $father->children_ids = array_diff($father->children_ids ?? [], [$person->id]);
                        $father->save();
                    }
                }
                if ($person->mother_id) {
                    $mother = People::find($person->mother_id);
                    if ($mother) {
                        $mother->children_ids = array_diff($mother->children_ids ?? [], [$person->id]);
                        $mother->save();
                    }
                }
                foreach ($person->children_ids ?? [] as $childId) {
                    $child = People::find($childId);
                    if ($child) {
                        if ($child->father_id == $person->id) {
                            $child->father_id = null;
                        }
                        if ($child->mother_id == $person->id) {
                            $child->mother_id = null;
                        }
                        $child->save();
                    }
                }
                foreach ($person->partners_ids ?? [] as $partnerId) {
                    $partner = People::find($partnerId);
                    if ($partner) {
                        $partner->partners_ids = array_diff($partner->partners_ids ?? [], [$person->id]);
                        $partner->save();
                    }
                }
                $person->delete();
            }
        }

        // حذف از access_ids کاربران
        $users = User::whereJsonContains('access_ids', $peopleIds)->get();
        foreach ($users as $user) {
            $user->access_ids = array_diff($user->access_ids ?? [], $peopleIds);
            $user->save();
        }

        // حذف درخواست شجره‌نامه
        $familyTreeRequest->delete();

        return redirect()->route('admin.panel')->with('success', 'شجره‌نامه با موفقیت حذف شد.');
    }
}