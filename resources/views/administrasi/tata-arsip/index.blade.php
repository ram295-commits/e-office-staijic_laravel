@extends('layouts.app')
@section('title', 'Tata Arsip & SOP')
@section('page-title', 'Tata Arsip & Pedoman')

@section('content')
<div class="space-y-6">
    <!-- SOP Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-white">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                    <i class="ph ph-books text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">{{ $sop->title }}</h3>
                    <p class="text-sm text-gray-500">Pedoman resmi penomoran dan pengarsipan dokumen</p>
                </div>
            </div>
            
            @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                <button type="button" onclick="document.getElementById('sop-view-mode').style.display='none'; document.getElementById('sop-edit-mode').style.display='block';" class="btn btn-secondary text-sm flex items-center gap-2">
                    <i class="ph ph-pencil"></i> <span>Edit Panduan</span>
                </button>
            @endif
        </div>
        
        <!-- View Mode -->
        <div id="sop-view-mode" class="p-6 prose max-w-none text-gray-700 text-sm">
            {!! $sop->content !!}
        </div>

        <!-- Edit Mode -->
        @if(auth()->user()->isAdmin() || auth()->user()->isManager())
        <div id="sop-edit-mode" style="display: none;" class="p-6 bg-gray-50/50">
            <form action="{{ route('administrasi.tata-arsip.update-sop') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group mb-4">
                    <label class="form-label" for="content">Konten Panduan (Mendukung HTML dasar)</label>
                    <textarea name="content" id="content" rows="15" class="form-control font-mono text-sm" required>{{ $sop->content }}</textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('sop-view-mode').style.display='block'; document.getElementById('sop-edit-mode').style.display='none';" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
        @endif
    </div>

    <!-- Templates Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-white">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-full bg-secondary/20 flex items-center justify-center text-primary">
                    <i class="ph ph-file-doc text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Template Dokumen Resmi</h3>
                    <p class="text-sm text-gray-500">Unduh format surat standar institusi</p>
                </div>
            </div>
            @if(auth()->user()->isAdmin())
            <button class="btn btn-secondary text-sm">
                <i class="ph ph-plus"></i> Tambah Template
            </button>
            @endif
        </div>
        
        <div class="p-6">
            @if($templates->isEmpty())
                <div class="text-center py-8">
                    <i class="ph ph-files text-4xl text-gray-300 mb-2"></i>
                    <p class="text-sm text-gray-500">Belum ada template dokumen yang tersedia.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($templates as $template)
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-secondary transition duration-200 group flex flex-col">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2 text-primary font-bold">
                                <i class="ph ph-file-text text-xl"></i> {{ $template->name }}
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mb-4 flex-1">{{ $template->description }}</p>
                        <a href="{{ Storage::url($template->file_path) }}" target="_blank" class="block w-full text-center bg-gray-50 hover:bg-secondary hover:text-white text-primary border border-gray-200 font-semibold py-1.5 rounded text-sm transition-colors">
                            <i class="ph ph-download-simple"></i> Unduh
                        </a>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
