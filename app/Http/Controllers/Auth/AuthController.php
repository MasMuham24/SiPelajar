<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $login = $request->input('username');
        $password = $request->input('password');

        // Allow login using username or nisn
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $field => $login,
            'password' => $password
        ];

        if (! Auth::attempt($credentials)) {
            return back()
                ->withInput()
                ->withErrors([
                    'username' => 'Username/NISN atau password salah.',
                ]);
        }

        $request->session()->regenerate();

        return match (Auth::user()->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'guru' => redirect()->route('guru.dashboard'),
            'siswa' => redirect()->route('siswa.dashboard'),
            default => abort(403),
        };
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Menampilkan form edit profil pengguna.
     */
    public function showProfile()
    {
        $user = Auth::user();
        return view('auth.profile.edit', compact('user'));
    }

    /**
     * Memperbarui informasi profil pengguna.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();
        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * Menampilkan form ubah kata sandi.
     */
    public function editPassword()
    {
        return view('auth.profile.change_password');
    }

    /**
     * Memperbarui kata sandi pengguna.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'current_password' => 'required|current-password',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $user->password = Hash::make($request->password);
        $user->save();
        return back()->with('success', 'Kata sandi berhasil diperbarui!');
    }
}
