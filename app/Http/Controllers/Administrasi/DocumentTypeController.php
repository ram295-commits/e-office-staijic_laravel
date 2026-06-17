<?php

namespace App\Http\Controllers\Administrasi;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use App\Models\Unit;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
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
        $documentTypes = DocumentType::with('unit')->orderBy('code')->paginate(15);
        return view('administrasi.document-types.index', compact('documentTypes'));
    }

    public function create()
    {
        $this->requireAdmin();
        $units = Unit::orderBy('name')->get();
        return view('administrasi.document-types.create', compact('units'));
    }

    public function store(Request $request)
    {
        $this->requireAdmin();
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'code' => 'required|string|max:50|unique:document_types,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        DocumentType::create($validated);

        return redirect()->route('administrasi.document-types.index')->with('success', 'Jenis Surat berhasil ditambahkan.');
    }

    public function edit(DocumentType $documentType)
    {
        $this->requireAdmin();
        $units = Unit::orderBy('name')->get();
        return view('administrasi.document-types.edit', compact('documentType', 'units'));
    }

    public function update(Request $request, DocumentType $documentType)
    {
        $this->requireAdmin();
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'code' => 'required|string|max:50|unique:document_types,code,' . $documentType->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $documentType->update($validated);

        return redirect()->route('administrasi.document-types.index')->with('success', 'Jenis Surat berhasil diperbarui.');
    }

    public function destroy(DocumentType $documentType)
    {
        $this->requireAdmin();
        // Ideally check if this type is used in mails
        // if ($documentType->mails()->count() > 0) { ... }
        
        $documentType->delete();
        return redirect()->route('administrasi.document-types.index')->with('success', 'Jenis Surat berhasil dihapus.');
    }
}
