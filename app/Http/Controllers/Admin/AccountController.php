<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function index(Request $request)
    {
        $search = $request->query('search');
        $role = $request->query('role');
        $sort = $request->query('sort', 'name');
        $direction = $request->query('direction', 'asc');

        $allowedSorts = ['name', 'username', 'email', 'role', 'created_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'name';
        }

        $allowedDirections = ['asc', 'desc'];
        if (!in_array($direction, $allowedDirections)) {
            $direction = 'asc';
        }

        $users = User::query();

        if ($search) {
            $users->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('username', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($role && in_array($role, ['admin', 'guru', 'siswa'])) {
            $users->where('role', $role);
        }

        $users = $users->orderBy($sort, $direction)->paginate(15)->appends($request->except('page'));

        return view('admin.accounts.index', compact('users', 'search', 'role', 'sort', 'direction'));
    }

    public function create()
    {
        return view('admin.accounts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => ['required', Rule::in(['admin', 'guru', 'siswa'])],
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('admin.accounts.index')
            ->with('success', 'Akun berhasil dibuat');
    }

    public function show(User $account)
    {
        return view('admin.accounts.show', compact('account'));
    }

    public function edit(User $account)
    {
        return view('admin.accounts.edit', compact('account'));
    }

    public function update(Request $request, User $account)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'username')->ignore($account->id),
            ],
            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($account->id),
            ],
            'password' => 'nullable|string|min:6|confirmed',
            'role' => ['required', Rule::in(['admin', 'guru', 'siswa'])],
        ]);

        $account->update([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        if ($request->password) {
            $account->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return redirect()->route('admin.accounts.index')
            ->with('success', 'Akun berhasil diperbarui');
    }

    public function destroy(User $account)
    {
        $account->delete();
        return redirect()->route('admin.accounts.index')
            ->with('success', 'Akun berhasil dihapus');
    }

    public function bulkDestroy()
    {
        $rawIds = request()->input('ids', '');
        $ids = is_array($rawIds) ? $rawIds : explode(',', (string) $rawIds);
        $ids = array_filter(array_map('trim', $ids));
        
        if (empty($ids)) {
            return redirect()->route('admin.accounts.index')->with('error', 'Pilih minimal 1 akun untuk dihapus.');
        }

        User::destroy($ids);

        return redirect()->route('admin.accounts.index')->with('success', 'Berhasil menghapus ' . count($ids) . ' akun.');
    }
}
