<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'totalCategories' => Category::count(),
            'totalBooks' => Book::count(),
            'totalUsers' => User::count(),
            'totalOrders' => Order::count(),
            'recentOrders' => Order::with('user')->latest()->take(5)->get(),
        ]);
    }
}
