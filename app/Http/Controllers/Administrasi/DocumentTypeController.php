<?php

namespace App\Http\Controllers\Administrasi;

use App\Exports\DocumentTypeExport;
use App\Http\Controllers\Controller;
use App\Imports\DocumentTypeImport;
use App\Models\DocumentType;
use App\Models\Unit;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DocumentTypeController extends Controller
{
    private function requireAdmin(): void
    {
        if (!auth()->user()?->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
    }

    public function index(Request $request)
    {
        $this->requireAdmin();

        // Allowed sort columns (map UI key → DB column)
        $sortable = [
            'name'        => 'document_types.name',
            'code'        => 'document_types.code',
            'unit'        => 'units.name',
            'description' => 'document_types.description',
        ];

        $sort      = $request->get('sort', 'code');
        $direction = $request->get('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        // Fall back to default if an invalid column is supplied
        if (!array_key_exists($sort, $sortable)) {
            $sort = 'code';
        }

        $documentTypes = DocumentType::with('unit')
            ->leftJoin('units', 'units.id', '=', 'document_types.unit_id')
            ->select('document_types.*')
            ->orderBy($sortable[$sort], $direction)
            ->paginate(15)
            ->withQueryString();

        return view('administrasi.document-types.index', compact('documentTypes', 'sort', 'direction'));
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
            'unit_id'     => 'required|exists:units,id',
            'code'        => 'required|string|max:50|unique:document_types,code',
            'name'        => 'required|string|max:255',
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
            'unit_id'     => 'required|exists:units,id',
            'code'        => 'required|string|max:50|unique:document_types,code,' . $documentType->id,
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $documentType->update($validated);

        return redirect()->route('administrasi.document-types.index')->with('success', 'Jenis Surat berhasil diperbarui.');
    }

    public function destroy(DocumentType $documentType)
    {
        $this->requireAdmin();
        $documentType->delete();
        return redirect()->route('administrasi.document-types.index')->with('success', 'Jenis Surat berhasil dihapus.');
    }

    // ── Export ────────────────────────────────────────────────────────────────

    public function export()
    {
        $this->requireAdmin();
        $filename = 'jenis-surat-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new DocumentTypeExport(), $filename);
    }

    // ── Import ────────────────────────────────────────────────────────────────

    public function import(Request $request)
    {
        $this->requireAdmin();

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ], [
            'file.required' => 'Pilih file Excel/CSV terlebih dahulu.',
            'file.mimes'    => 'Format file harus .xlsx, .xls, atau .csv.',
            'file.max'      => 'Ukuran file maksimal 2 MB.',
        ]);

        $importer = new DocumentTypeImport();
        Excel::import($importer, $request->file('file'));

        $failures = $importer->failures();
        $errors   = $importer->errors();

        if ($importer->imported === 0 && ($failures->count() > 0 || count($errors) > 0)) {
            $msgs = [];
            foreach ($failures as $failure) {
                $msgs[] = 'Baris ' . $failure->row() . ': ' . implode(', ', $failure->errors());
            }
            return redirect()->route('administrasi.document-types.index')
                ->with('error', 'Import gagal. ' . implode(' | ', array_slice($msgs, 0, 5)));
        }

        $msg = "{$importer->imported} jenis surat berhasil diimpor.";
        if ($importer->skipped > 0) {
            $msg .= " {$importer->skipped} baris dilewati (kode duplikat atau tidak valid).";
        }

        return redirect()->route('administrasi.document-types.index')->with('success', $msg);
    }
}
