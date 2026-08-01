<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeacherRequest;
use App\Models\Teacher;
use Illuminate\Support\Facades\Storage;

class TeacherController extends Controller
{
    public function index()
    {
        $search = request('search');
        $teachers = Teacher::query()->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")->orWhere('nip', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('teachers.index', compact(
            'teachers',
            'search'
        ));
    }

    public function create()
    {
        return view('teachers.create');
    }

    public function store(TeacherRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request
                ->file('photo')
                ->store('teachers', 'public');
        }

        Teacher::create($data);

        return redirect()
            ->route('teachers.index')
            ->with('success', 'Data guru berhasil ditambahkan.');
    }

    public function show(Teacher $teacher)
    {
        return view('teachers.show', compact(
            'teacher'
        ));
    }

    public function edit(Teacher $teacher)
    {
        return view('teachers.edit', compact(
            'teacher'
        ));
    }

    public function update(
        TeacherRequest $request,
        Teacher $teacher
    ) {
        $data = $request->validated();

        if ($request->hasFile('photo')) {

            if (
                $teacher->photo &&
                Storage::disk('public')->exists($teacher->photo)
            ) {
                Storage::disk('public')->delete(
                    $teacher->photo
                );
            }

            $data['photo'] = $request
                ->file('photo')
                ->store('teachers', 'public');
        }

        $teacher->update($data);

        return redirect()
            ->route('teachers.index')
            ->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Teacher $teacher)
    {
        if (
            $teacher->photo &&
            Storage::disk('public')->exists($teacher->photo)
        ) {
            Storage::disk('public')->delete(
                $teacher->photo
            );
        }

        $teacher->delete();

        return redirect()
            ->route('teachers.index')
            ->with('success', 'Data guru berhasil dihapus.');
    }
}
