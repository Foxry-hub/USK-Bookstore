<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    /**
     * Tampilkan daftar pesan masuk dari customer.
     */
    public function index(): View
    {
        return view('admin.messages.index', [
            'messages' => ContactMessage::with('user')->latest()->paginate(10),
        ]);
    }

    /**
     * Tampilkan detail pesan secara read-only.
     */
    public function show(ContactMessage $message): View
    {
        if (! $message->is_read) {
            $message->forceFill([
                'is_read' => true,
                'read_at' => now(),
            ])->save();
        }

        return view('admin.messages.show', [
            'message' => $message->load('user'),
        ]);
    }
}