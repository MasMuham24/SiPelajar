<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use Illuminate\Http\Request;

class AttendanceSettingController extends Controller
{
    /**
     * Tampilkan halaman pengaturan jam absensi.
     */
    public function index()
    {
        $setting = AttendanceSetting::getSettings();

        return view('admin.attendance_settings.index', compact('setting'));
    }

    /**
     * Update pengaturan jam absensi sekolah.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'school_start_time' => ['required'],
            'school_end_time' => ['required', 'after:school_start_time'],
        ], [
            'school_start_time.required' => 'Jam masuk wajib diisi.',
            'school_end_time.required' => 'Jam pulang wajib diisi.',
            'school_end_time.after' => 'Jam pulang harus lebih besar daripada jam masuk.',
        ]);

        $setting = AttendanceSetting::getSettings();
        $setting->update([
            'school_start_time' => $validated['school_start_time'],
            'school_end_time' => $validated['school_end_time'],
        ]);

        return redirect()->route('admin.attendance-settings.index')
            ->with('success', 'Pengaturan jam absensi berhasil diperbarui.');
    }
}
