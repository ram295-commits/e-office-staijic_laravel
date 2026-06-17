<?php

namespace App\Http\Controllers;

use App\Exports\MailTemplateExport;
use App\Imports\MailsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MailImportController extends Controller
{
    /**
     * Show import form.
     */
    public function index()
    {
        return view('mails.import');
    }

    /**
     * Download the Excel import template.
     */
    public function downloadTemplate()
    {
        return Excel::download(new MailTemplateExport, 'template-import-surat.xlsx');
    }

    /**
     * Handle the Excel import action.
     */
    public function store(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            DB::beginTransaction();

            Excel::import(new MailsImport, $request->file('excel_file'));

            DB::commit();

            return redirect()->route('mails.archive.index')
                ->with('success', 'Data surat berhasil di-import secara massal!');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            $failures = $e->failures();
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = "Baris {$failure->row()} (Kolom {$failure->attribute()}): " . implode(', ', $failure->errors());
            }
            return back()->withInput()->withErrors($errors);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors([$e->getMessage()]);
        }
    }
}
