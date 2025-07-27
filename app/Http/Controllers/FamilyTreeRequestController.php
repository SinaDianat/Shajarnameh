<?php

namespace App\Http\Controllers;

use App\Models\FamilyTreeRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FamilyTreeRequestController extends Controller
{
    public function create()
    {
        return view('family_tree_requests.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        FamilyTreeRequest::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        return redirect()->route('user.panel')->with('success', 'درخواست شجرنامه ارسال شد.');
    }

    public function index()
    {
        if (!Auth::user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'فقط ادمین‌ها می‌توانند درخواست‌ها را ببینند.');
        }

        $requests = FamilyTreeRequest::with('user')->get();
        return view('admin.family_tree_requests', compact('requests'));
    }

    public function approve($id)
    {
        if (!Auth::user()->is_admin) {
            return redirect()->route('user.dashboard')->with('error', 'فقط ادمین‌ها می‌توانند درخواست‌ها را تأیید کنند.');
        }

        $request = FamilyTreeRequest::findOrFail($id);
        $request->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        return redirect()->route('admin.panel')->with('success', 'درخواست تأیید شد.');
    }

    public function reject($id)
    {
        if (!Auth::user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'فقط ادمین‌ها می‌توانند درخواست‌ها را رد کنند.');
        }

        $request = FamilyTreeRequest::findOrFail($id);
        $request->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        return redirect()->route('family_tree_requests.index')->with('success', 'درخواست رد شد.');
    }
}