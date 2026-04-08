<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactMessageController extends Controller
{
    // Contact form hanya bisa diisi user biasa, admin ngga boleh.
    public function store(Request $request): RedirectResponse
    {
        if (!Auth::check() || Auth::user()?->is_admin) {
            return redirect()->route('login');
        }

        $validated = $this->validateContactMessage($request);

        ContactMessage::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'email' => Auth::user()->email,
            'message' => $validated['message'],
        ]);

        return back()->with('success', 'Pesan kamu sudah terkirim ke admin.');
    }

    private function validateContactMessage(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);
    }
}
