<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MajorRequest;
use App\Models\Major;

class MajorController extends Controller
{
    public function index()
    {
        $majors = Major::query()->latest()->paginate(10);
        return view('admin.majors.index', compact('majors'));
    }

    public function create()
    {
        return view('admin.majors.create');
    }

    public function store(MajorRequest $request)
    {
        Major::create($request->validated());

        return redirect()
            ->route('admin.majors.index')
            ->with('success', 'Jurusan berhasil ditambahkan');
    }

    public function show(Major $major)
    {
        return view('admin.majors.show', compact('major'));
    }

    public function edit(Major $major)
    {
        return view('admin.majors.edit', compact('major'));
    }

    public function update(MajorRequest $request, Major $major)
    {
        $major->update(
            $request->validated()
        );

        return redirect()
            ->route('admin.majors.index')
            ->with('success', 'Jurusan berhasil diperbarui');
    }

    public function destroy(Major $major)
    {
        Major::destroy($major->id);

        return redirect()
            ->route('admin.majors.index')
            ->with('success', 'Jurusan berhasil dihapus');
    }
}
