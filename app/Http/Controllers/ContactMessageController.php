<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactMessageController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if (! Auth::check() || Auth::user()?->is_admin) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        ContactMessage::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'email' => Auth::user()->email,
            'message' => $validated['message'],
        ]);

        return back()->with('success', 'Pesan kamu sudah terkirim ke admin.');
    }
}