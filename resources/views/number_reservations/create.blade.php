@extends('layouts.app')
@section('title', 'Ajukan Reservasi Nomor')
@section('page-title', 'Ajukan Reservasi Nomor')

@section('content')
<div class="breadcrumb mb-6">
    <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-primary transition-colors">Dashboard</a>
    <i class="ph ph-caret-right text-gray-400" style="font-size:12px; margin: 0 8px;"></i>
    <a href="{{ route('number_reservations.index') }}" class="text-gray-500 hover:text-primary transition-colors">Reservasi Nomor</a>
    <i class="ph ph-caret-right text-gray-400" style="font-size:12px; margin: 0 8px;"></i>
    <span class="text-gray-800 font-medium">Ajukan</span>
</div>

@php
    $userUnits = auth()->user()->isAdmin() ? \App\Models\Unit::all() : auth()->user()->units;
    $allowedUnitIds = $userUnits->pluck('id');
    $groupedDocTypes = $documentTypes->whereIn('unit_id', $allowedUnitIds)->groupBy('unit.name');
@endphp

<div x-data="{
    documentTypes: {{ $documentTypes->map(fn($t) => ['id' => $t->id, 'code' => $t->code, 'name' => $t->name, 'unit_code' => $t->unit ? $t->unit->code : '[KODE_UNIT]'])->toJson() }},
    letterFormats: {{ $letterFormats->map(fn($f) => ['id' => $f->id, 'format_string' => $f->format_string, 'type' => $f->type])->toJson() }},
    
    selectedDocType: '{{ old('document_type_id') }}',
    selectedFormat: '{{ old('letter_format_id') }}',
    backdateTarget: '{{ old('backdate_target', date('Y-m-d')) }}',
    quantity: {{ old('quantity', 1) }},

    get selectedDocTypeCode() {
        const doc = this.documentTypes.find(d => d.id == this.selectedDocType);
        return doc ? doc.code : '[KODE_UNIT]';
    },

    get selectedUnitCode() {
        const doc = this.documentTypes.find(d => d.id == this.selectedDocType);
        return doc ? doc.unit_code : '[KODE_UNIT]';
    },

    get selectedFormatString() {
        const fmt = this.letterFormats.find(f => f.id == this.selectedFormat);
        return fmt ? fmt.format_string : '[FORMAT_BELUM_DIPILIH]';
    },

    get targetYear() {
        if (!this.backdateTarget) return new Date().getFullYear();
        const parts = this.backdateTarget.split('-');
        if (parts.length > 0) return parts[0];
        return new Date().getFullYear();
    },

    get targetRomanMonth() {
        if (!this.backdateTarget) return 'I';
        const parts = this.backdateTarget.split('-');
        if (parts.length < 2) return 'I';
        const monthNum = parseInt(parts[1], 10);
        const romans = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        return romans[monthNum - 1] || 'I';
    },

    get previews() {
        const fmtStr = this.selectedFormatString;
        if (fmtStr === '[FORMAT_BELUM_DIPILIH]') {
            return ['Pilih Jenis Dokumen dan Format Penomoran untuk melihat preview...'];
        }
        
        const unitCode = this.selectedUnitCode;
        const year = this.targetYear;
        const romanMonth = this.targetRomanMonth;
        const qty = Math.min(Math.max(parseInt(this.quantity) || 1, 1), 5);
        
        const list = [];
        for (let i = 0; i < qty; i++) {
            const seqStr = String(i + 1).padStart(3, '0');
            let preview = fmtStr
                .replaceAll('[NO_URUT]', seqStr)
                .replaceAll('[KODE_UNIT]', unitCode)
                .replaceAll('[BULAN_ROMAWI]', romanMonth)
                .replaceAll('[TAHUN]', String(year));
            list.push(preview);
        }
        
        const actualQty = parseInt(this.quantity) || 1;
        if (actualQty > 5) {
            list.push('... dan ' + (actualQty - 5) + ' nomor surat lainnya');
        }
        
        return list;
    }
}">
    <form method="POST" action="{{ route('number_reservations.store') }}">
        @csrf

        <div style="display:grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start;">
            <!-- Left Card: Form Inputs -->
            <div class="space-y-6">
                <div class="card">
                    <h3 class="card-title">
                        <i class="ph ph-note-pencil text-primary text-xl"></i>
                        Form Pengajuan Reservasi Nomor
                    </h3>

                    <div class="form-group">
                        <label class="form-label" for="document_type_id">Jenis Dokumen <span class="req">*</span></label>
                        <select id="document_type_id" name="document_type_id" class="form-control" x-model="selectedDocType" required>
                            <option value="">— Pilih Jenis Dokumen —</option>
                            @foreach($groupedDocTypes as $unitName => $types)
                                <optgroup label="{{ $unitName }}">
                                    @foreach($types as $typeOption)
                                        <option value="{{ $typeOption->id }}">
                                            {{ $typeOption->code }} - {{ $typeOption->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="letter_format_id">Format Penomoran <span class="req">*</span></label>
                        <select id="letter_format_id" name="letter_format_id" class="form-control" x-model="selectedFormat" required>
                            <option value="">— Pilih Format Penomoran —</option>
                            @foreach($letterFormats as $formatOption)
                                <option value="{{ $formatOption->id }}">
                                    {{ ucfirst($formatOption->type) }} ({{ $formatOption->format_string }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label" for="backdate_target">Tanggal Target Backdate <span class="req">*</span></label>
                            <input type="date" id="backdate_target" name="backdate_target" class="form-control" x-model="backdateTarget" max="{{ date('Y-m-d') }}" required>
                            <span class="text-xs text-gray-400 mt-1 block">Hanya diperbolehkan tanggal hari ini atau ke belakang (backdate).</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="quantity">Jumlah Slot yang Direservasi <span class="req">*</span></label>
                            <input type="number" id="quantity" name="quantity" class="form-control" x-model="quantity" min="1" max="100" required>
                            <span class="text-xs text-gray-400 mt-1 block">Minimal 1 slot.</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="reason">Alasan Reservasi <span class="req">*</span></label>
                        <textarea id="reason" name="reason" class="form-control" rows="4" placeholder="Ketik alasan mendetail (contoh: Surat keputusan rapat tanggal {{ date('d/m/Y', strtotime('-7 days')) }} yang terlewat penomorannya)" required>{{ old('reason') }}</textarea>
                    </div>

                    <div class="flex gap-3 justify-end mt-6">
                        <a href="{{ route('number_reservations.index') }}" class="btn btn-secondary">
                            <i class="ph ph-x"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ph ph-paper-plane-tilt"></i> Kirim Pengajuan
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Column: Live Preview & Guidance -->
            <div class="space-y-6">
                <!-- Preview Card -->
                <div class="card bg-gray-900 text-white border-transparent">
                    <h3 class="text-md font-bold text-green-400 flex items-center gap-2 mb-3">
                        <i class="ph ph-eye-closed"></i> Live Preview Format
                    </h3>
                    <p class="text-xs text-gray-400 mb-4 leading-relaxed">
                        Tampilan perkiraan format nomor surat berdasarkan data pilihan Anda:
                    </p>

                    <div class="space-y-2 font-mono text-sm bg-gray-950/80 p-4 rounded border border-gray-800 shadow-inner">
                        <template x-for="(preview, index) in previews" :key="index">
                            <div class="flex items-center gap-2 text-lime-400 break-all select-all">
                                <span class="text-gray-600 text-xs shrink-0 select-none" x-text="(index+1) + '.'"></span>
                                <span x-text="preview"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Guidance Card -->
                <div class="card">
                    <h3 class="text-sm font-bold text-gray-800 flex items-center gap-1.5 mb-3">
                        <i class="ph ph-info text-primary"></i> Aturan Reservasi
                    </h3>
                    <ul class="text-xs text-gray-600 space-y-2 list-disc pl-4 leading-relaxed">
                        <li>Semua pengajuan reservasi masuk status <strong>Pending</strong> dan membutuhkan persetujuan <strong>Admin</strong>.</li>
                        <li>Sistem otomatis mencari slot urutan kosong (*vacant consecutive sequence*) terdekat pada tanggal target.</li>
                        <li><strong>Safe Backdate Guard:</strong> Tanggal target tidak boleh melanggar urutan kronologis yang merusak integritas alur waktu surat.</li>
                        <li>Tanggal surat pada slot yang telah disetujui bersifat <strong>Terkunci (Immutable)</strong> dan tidak bisa diganti di kemudian hari.</li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
