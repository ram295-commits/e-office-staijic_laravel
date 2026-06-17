<?php

namespace App\Http\Controllers\Administrasi;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UnitController extends Controller
{
    private function requireAdmin(): void
    {
        if (!auth()->user()?->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
    }

    public function index()
    {
        $this->requireAdmin();
        $units = Unit::orderBy('name')->paginate(15);
        return view('administrasi.units.index', compact('units'));
    }

    public function create()
    {
        $this->requireAdmin();
        return view('administrasi.units.create');
    }

    public function store(Request $request)
    {
        $this->requireAdmin();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:units,code',
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        Unit::create($validated);

        return redirect()->route('administrasi.units.index')->with('success', 'Unit berhasil ditambahkan.');
    }

    public function edit(Unit $unit)
    {
        $this->requireAdmin();
        return view('administrasi.units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $this->requireAdmin();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:units,code,' . $unit->id,
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $unit->update($validated);

        return redirect()->route('administrasi.units.index')->with('success', 'Unit berhasil diperbarui.');
    }

    public function destroy(Unit $unit)
    {
        $this->requireAdmin();
        if ($unit->documentTypes()->count() > 0) {
            return back()->with('error', 'Unit tidak dapat dihapus karena masih memiliki jenis dokumen terkait.');
        }

        $unit->delete();
        return redirect()->route('administrasi.units.index')->with('success', 'Unit berhasil dihapus.');
    }
}
