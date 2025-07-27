<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\FamilyTreeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $requests = FamilyTreeRequest::where('user_id', $user->id)->get();
        $approvedRequest = FamilyTreeRequest::where('user_id', $user->id)->where('status', 'approved')->first();
        return view('user.user-panel', compact('user', 'requests', 'approvedRequest'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        try {
            $user->name = $validated['name'];
            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }
            $user->save();
            return redirect()->route('user.panel')->with('success', 'پروفایل با موفقیت به‌روزرسانی شد.');
        } catch (\Exception $e) {
            Log::error('Error updating profile: ' . $e->getMessage());
            return redirect()->back()->with('error', 'خطایی در به‌روزرسانی پروفایل رخ داد: ' . $e->getMessage());
        }
    }
}