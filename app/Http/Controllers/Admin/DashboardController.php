<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = \App\Models\User::count();
        $currentYearCount = \App\Models\Student::count(); // Contoh data
        $completeClasses = \App\Models\Classroom::count(); // Contoh data
        
        return view('admin.dashboard', compact('totalUsers', 'currentYearCount', 'completeClasses'));
    }
}
