@extends('layouts.app')
@section('title', 'Import Data Surat')
@section('page-title', 'Import Data Surat secara Massal')

@section('content')
<div class="space-y-6">
    <!-- Info Card / Guide -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        <div class="flex items-start gap-4">
            <div class="h-12 w-12 rounded-lg bg-green-50 flex items-center justify-center text-primary shrink-0">
                <i class="ph ph-info text-2xl"></i>
            </div>
            <div class="flex-1">
                <h3 class="font-bold text-gray-800 text-lg">Petunjuk Import Data Surat</h3>
                <p class="text-sm text-gray-600 mt-1 leading-relaxed">
                    Fitur ini memungkinkan Anda untuk meng-import banyak data surat sekaligus menggunakan file Excel (.xlsx). 
                    Silakan ikuti instruksi di bawah ini untuk memastikan data ter-import dengan benar tanpa kesalahan.
                </p>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="border border-gray-100 rounded-md p-4 bg-gray-50/50">
                        <div class="font-semibold text-sm text-gray-800 flex items-center gap-2">
                            <span class="h-5 w-5 rounded-full bg-green-100 text-primary flex items-center justify-center text-xs font-bold">1</span>
                            Unduh Template
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Unduh template Excel resmi melalui tombol di sebelah kanan untuk melihat struktur kolom yang tepat.</p>
                    </div>
                    <div class="border border-gray-100 rounded-md p-4 bg-gray-50/50">
                        <div class="font-semibold text-sm text-gray-800 flex items-center gap-2">
                            <span class="h-5 w-5 rounded-full bg-green-100 text-primary flex items-center justify-center text-xs font-bold">2</span>
                            Isi Data & Validasi
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Pastikan "Kode Surat" sesuai jenis dokumen terdaftar dan urutan tanggal/nomor urut memenuhi aspek kronologis (Anti-Collision).</p>
                    </div>
                    <div class="border border-gray-100 rounded-md p-4 bg-gray-50/50">
                        <div class="font-semibold text-sm text-gray-800 flex items-center gap-2">
                            <span class="h-5 w-5 rounded-full bg-green-100 text-primary flex items-center justify-center text-xs font-bold">3</span>
                            Unggah & Simpan
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Unggah file Anda. Sistem akan memproses secara transaksional (Rollback otomatis jika salah satu data gagal).</p>
                    </div>
                </div>
            </div>
            <div class="shrink-0">
                <a href="{{ route('mails.import.template') }}" class="btn btn-primary flex items-center gap-2 hover:scale-[1.02] transition-transform">
                    <i class="ph ph-download-simple text-lg"></i> Unduh Template Excel
                </a>
            </div>
        </div>
    </div>

    <!-- Upload Form and Preview -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Upload Form -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 lg:col-span-1">
            <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="ph ph-file-arrow-up text-lg text-primary"></i> Unggah File Excel
            </h4>
            
            <form action="{{ route('mails.import.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary/50 transition-colors bg-gray-50/50 cursor-pointer group" onclick="document.getElementById('excel_file').click()">
                    <input type="file" name="excel_file" id="excel_file" class="hidden" accept=".xlsx,.xls,.csv" onchange="updateFileName(this)">
                    <div class="space-y-2">
                        <i class="ph ph-file-xls text-4xl text-gray-400 group-hover:text-primary transition-colors"></i>
                        <div class="text-sm font-semibold text-gray-700" id="upload-label">Pilih file atau seret ke sini</div>
                        <p class="text-xs text-gray-400">Hanya menerima format .xlsx, .xls, atau .csv (Maks. 10MB)</p>
                    </div>
                </div>

                <div class="text-sm text-gray-500 hidden" id="file-details">
                    File terpilih: <span class="font-bold text-gray-800" id="file-name"></span>
                </div>

                <button type="submit" class="w-full btn btn-primary py-2.5 flex items-center justify-center gap-2">
                    <i class="ph ph-check text-lg"></i> Proses & Import Data
                </button>
                <a href="{{ route('mails.archive.index') }}" class="w-full btn btn-secondary py-2.5 flex items-center justify-center gap-2">
                    Batal
                </a>
            </form>
        </div>

        <!-- Header Mapping Preview -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 lg:col-span-2">
            <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="ph ph-table text-lg text-primary"></i> Panduan Kolom Template Excel
            </h4>
            <div class="overflow-x-auto border border-gray-150 rounded-lg">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-150 text-gray-700 font-semibold">
                            <th class="p-3">Nama Kolom</th>
                            <th class="p-3">Tipe Data</th>
                            <th class="p-3">Deskripsi / Contoh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-600">
                        <tr>
                            <td class="p-3 font-semibold text-gray-800">Tipe</td>
                            <td class="p-3"><span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 text-xs font-semibold">Enum</span></td>
                            <td class="p-3">Pilihan: <code class="bg-gray-100 px-1 py-0.5 rounded">incoming</code> (masuk), <code class="bg-gray-100 px-1 py-0.5 rounded">outgoing</code> (keluar), <code class="bg-gray-100 px-1 py-0.5 rounded">internal</code></td>
                        </tr>
                        <tr>
                            <td class="p-3 font-semibold text-gray-800">Subjek</td>
                            <td class="p-3"><span class="px-2 py-0.5 rounded bg-yellow-50 text-yellow-700 text-xs font-semibold">String</span></td>
                            <td class="p-3">Judul atau perihal surat</td>
                        </tr>
                        <tr>
                            <td class="p-3 font-semibold text-gray-800">Isi</td>
                            <td class="p-3"><span class="px-2 py-0.5 rounded bg-yellow-50 text-yellow-700 text-xs font-semibold">Text</span></td>
                            <td class="p-3">Deskripsi / isi lengkap surat</td>
                        </tr>
                        <tr>
                            <td class="p-3 font-semibold text-gray-800">Kode Surat</td>
                            <td class="p-3"><span class="px-2 py-0.5 rounded bg-purple-50 text-purple-700 text-xs font-semibold">String</span></td>
                            <td class="p-3">Kode jenis surat terdaftar, misal: <code class="bg-gray-100 px-1 py-0.5 rounded">SK</code>, <code class="bg-gray-100 px-1 py-0.5 rounded">ED</code>, <code class="bg-gray-100 px-1 py-0.5 rounded">MOU</code></td>
                        </tr>
                        <tr>
                            <td class="p-3 font-semibold text-gray-800">Tanggal Surat</td>
                            <td class="p-3"><span class="px-2 py-0.5 rounded bg-green-50 text-green-700 text-xs font-semibold">Date</span></td>
                            <td class="p-3">Format: <code class="bg-gray-100 px-1 py-0.5 rounded">YYYY-MM-DD</code> (Contoh: 2026-05-21)</td>
                        </tr>
                        <tr>
                            <td class="p-3 font-semibold text-gray-800">Nomor Urut</td>
                            <td class="p-3"><span class="px-2 py-0.5 rounded bg-gray-50 text-gray-600 text-xs font-semibold">Integer</span></td>
                            <td class="p-3">Opsional. Jika kosong, sistem otomatis menetapkan nomor urut kronologis berikutnya.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function updateFileName(input) {
        const fileDetails = document.getElementById('file-details');
        const fileName = document.getElementById('file-name');
        const uploadLabel = document.getElementById('upload-label');
        if (input.files && input.files.length > 0) {
            fileName.textContent = input.files[0].name;
            fileDetails.classList.remove('hidden');
            uploadLabel.textContent = "File Terpilih!";
        } else {
            fileDetails.classList.add('hidden');
            uploadLabel.textContent = "Pilih file atau seret ke sini";
        }
    }
</script>
@endsection
