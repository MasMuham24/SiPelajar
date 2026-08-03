<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfficeRequest;
use App\Models\Office;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $offices = Office::latest()->paginate(10);
        return view('admin.offices.index', compact('offices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.offices.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OfficeRequest $request)
    {
        Office::create($request->validated());
        return redirect()->route('admin.offices.index')->with('success', 'Lokasi Sekolah Berhasil Ditambahkan');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Office $office)
    {
        return view('admin.offices.edit', compact('office'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OfficeRequest $request, Office $office)
    {
        $office->update($request->validated());
        return redirect()->route('admin.offices.index')->with('success', 'Lokasi Sekolah Berhasil Diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Office $office)
    {
        $office->delete();
        return redirect()->route('admin.offices.index')->with('success', 'Lokasi Sekolah Berhasil Dihapus');
    }
}
