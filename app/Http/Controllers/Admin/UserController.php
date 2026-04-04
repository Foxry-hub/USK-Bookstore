<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Tampilkan semua user terdaftar buat monitoring admin.
     */
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::latest()->paginate(12),
        ]);
    }
}
