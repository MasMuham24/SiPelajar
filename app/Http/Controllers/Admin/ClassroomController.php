<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClassroomRequest;
use App\Models\Classroom;
use App\Models\Major;

class ClassroomController extends Controller
{
    public function index()
    {
        $classrooms = Classroom::with('major')
            ->latest()
            ->paginate(10);

        $majors = Major::all();

        return view('admin.classrooms.index', compact('classrooms', 'majors'));
    }

    public function create()
    {
        return redirect()->route('admin.classrooms.index');
    }

    public function store(ClassroomRequest $request)
    {
        Classroom::create($request->validated());

        return redirect()
            ->route('admin.classrooms.index')
            ->with('success', 'Kelas berhasil ditambahkan');
    }

    public function edit(Classroom $classroom)
    {
        return redirect()->route('admin.classrooms.index');
    }

    public function update(ClassroomRequest $request, Classroom $classroom)
    {
        $classroom->update(
            $request->validated()
        );

        return redirect()
            ->route('admin.classrooms.index')
            ->with('success', 'Kelas berhasil diperbarui');
    }

    public function destroy(Classroom $classroom)
    {
        Classroom::destroy($classroom->id);

        return redirect()->route('admin.classrooms.index')->with('success', 'Kelas berhasil dihapus');
    }

    public function bulkDestroy()
    {
        $rawIds = request()->input('ids', '');
        $ids = is_array($rawIds) ? $rawIds : explode(',', (string) $rawIds);
        $ids = array_filter(array_map('trim', $ids));
        
        if (empty($ids)) {
            return redirect()->route('admin.classrooms.index')->with('error', 'Pilih minimal 1 kelas untuk dihapus.');
        }

        Classroom::destroy($ids);

        return redirect()->route('admin.classrooms.index')->with('success', 'Berhasil menghapus ' . count($ids) . ' kelas.');
    }
}
