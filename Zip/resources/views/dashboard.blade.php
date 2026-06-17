@extends('layouts.app')
@section('title', 'Dashboard E-Office')
@section('page-title', 'Dashboard')

@section('content')

<!-- Welcome Section -->
<div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Selamat Datang, {{ auth()->user()->name ?? 'Administrator' }}! 👋</h2>
        <p class="text-gray-500 mt-1">Berikut adalah ringkasan aktivitas E-Office Anda hari ini.</p>
    </div>
    <a href="{{ route('mails.incoming.create') }}" class="bg-primary hover:bg-emerald-800 text-white font-semibold py-2.5 px-5 rounded-full transition duration-300 shadow-sm flex items-center gap-2 text-sm">
        <i class="ph ph-plus-circle text-lg"></i> Surat Masuk Baru
    </a>
</div>

<!-- Main Alpine.js Dashboard Component Wrapper -->
<div x-data="{
    mails: {{ json_encode($recentMails) }},
    filterType: 'all',
    filterStatus: 'all',
    searchQuery: '',
    selectedMail: null,
    dispositionFormOpen: false,

    get filteredMails() {
        return this.mails.filter(mail => {
            // Type Filter
            if (this.filterType !== 'all' && mail.type !== this.filterType) return false;
            
            // Status Filter
            if (this.filterStatus !== 'all') {
                if (this.filterStatus === 'my_pending_disposition') {
                    if (!mail.has_my_pending_disposition) return false;
                } else if (mail.status !== this.filterStatus) {
                    return false;
                }
            }
            
            // Search Query Filter
            if (this.searchQuery.trim() !== '') {
                const q = this.searchQuery.toLowerCase();
                const ref = (mail.reference_number || '').toLowerCase();
                const subj = (mail.subject || '').toLowerCase();
                const sender = (mail.sender_name || '').toLowerCase();
                const recipient = (mail.recipient_name || '').toLowerCase();
                return ref.includes(q) || subj.includes(q) || sender.includes(q) || recipient.includes(q);
            }
            
            return true;
        });
    },

    selectMail(mail) {
        this.selectedMail = mail;
        this.dispositionFormOpen = false;
    },

    setFilter(type, value) {
        if (type === 'type') {
            if (this.filterType === value) {
                this.filterType = 'all';
            } else {
                this.filterType = value;
                this.filterStatus = 'all';
            }
        } else if (type === 'status') {
            if (this.filterStatus === value) {
                this.filterStatus = 'all';
            } else {
                this.filterStatus = value;
                this.filterType = 'all';
            }
        }
    },

    resetFilters() {
        this.filterType = 'all';
        this.filterStatus = 'all';
        this.searchQuery = '';
    }
}" class="space-y-8">

    <!-- Clickable Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Stats Card: Incoming Mails -->
        <div @click="setFilter('type', 'incoming')" 
             :class="filterType === 'incoming' ? 'ring-2 ring-primary bg-emerald-50/50 border-primary' : 'bg-white border-gray-100 hover:shadow-md hover:-translate-y-0.5'"
             class="rounded-xl shadow-sm border p-6 flex items-center gap-5 cursor-pointer transition duration-300">
            <div class="h-14 w-14 rounded-full bg-emerald-50 flex items-center justify-center text-primary text-2xl shrink-0">
                <i class="ph ph-arrow-circle-down"></i>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-gray-800">{{ $stats['incoming_total'] ?? 0 }}</div>
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mt-1">Surat Masuk</div>
            </div>
        </div>

        <!-- Stats Card: Outgoing Mails -->
        <div @click="setFilter('type', 'outgoing')" 
             :class="filterType === 'outgoing' ? 'ring-2 ring-primary bg-emerald-50/50 border-primary' : 'bg-white border-gray-100 hover:shadow-md hover:-translate-y-0.5'"
             class="rounded-xl shadow-sm border p-6 flex items-center gap-5 cursor-pointer transition duration-300">
            <div class="h-14 w-14 rounded-full bg-lime-50 flex items-center justify-center text-secondary text-2xl shrink-0">
                <i class="ph ph-arrow-circle-up"></i>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-gray-800">{{ $stats['outgoing_total'] ?? 0 }}</div>
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mt-1">Surat Keluar</div>
            </div>
        </div>

        <!-- Stats Card: Pending Mails -->
        <div @click="setFilter('status', 'pending')" 
             :class="filterStatus === 'pending' ? 'ring-2 ring-primary bg-emerald-50/50 border-primary' : 'bg-white border-gray-100 hover:shadow-md hover:-translate-y-0.5'"
             class="rounded-xl shadow-sm border p-6 flex items-center gap-5 cursor-pointer transition duration-300">
            <div class="h-14 w-14 rounded-full bg-amber-50 flex items-center justify-center text-amber-600 text-2xl shrink-0">
                <i class="ph ph-clock"></i>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-gray-800">{{ $stats['pending_count'] ?? 0 }}</div>
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mt-1">Tindak Lanjut</div>
            </div>
        </div>

        <!-- Stats Card: My Pending Dispositions -->
        <div @click="setFilter('status', 'my_pending_disposition')" 
             :class="filterStatus === 'my_pending_disposition' ? 'ring-2 ring-primary bg-emerald-50/50 border-primary' : 'bg-white border-gray-100 hover:shadow-md hover:-translate-y-0.5'"
             class="rounded-xl shadow-sm border p-6 flex items-center gap-5 cursor-pointer transition duration-300">
            <div class="h-14 w-14 rounded-full bg-red-50 flex items-center justify-center text-red-600 text-2xl shrink-0">
                <i class="ph ph-git-branch"></i>
            </div>
            <div>
                <div class="text-3xl font-extrabold text-gray-800">{{ $stats['my_dispositions'] ?? 0 }}</div>
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mt-1">Disposisi Pending</div>
            </div>
        </div>

    </div>

    <!-- Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Side: Table & Monthly Chart -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Quick Filter and Search Bar -->
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                
                <!-- Search Input -->
                <div class="relative flex-1 max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ph ph-magnifying-glass text-gray-400 text-lg"></i>
                    </span>
                    <input type="text" x-model="searchQuery" placeholder="Cari nomor, perihal, atau pengirim..." class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-gray-700">
                </div>

                <!-- Filters Control -->
                <div class="flex flex-wrap items-center gap-2">
                    
                    <!-- Type Filter Group -->
                    <div class="flex bg-gray-100 p-0.5 rounded-lg text-[10px] font-bold text-gray-600">
                        <button @click="filterType = 'all'" :class="filterType === 'all' ? 'bg-white text-primary shadow-sm' : 'hover:text-gray-900'" class="px-2.5 py-1.5 rounded transition-all">Semua Tipe</button>
                        <button @click="setFilter('type', 'incoming')" :class="filterType === 'incoming' ? 'bg-white text-primary shadow-sm' : 'hover:text-gray-900'" class="px-2.5 py-1.5 rounded transition-all">Masuk</button>
                        <button @click="setFilter('type', 'outgoing')" :class="filterType === 'outgoing' ? 'bg-white text-primary shadow-sm' : 'hover:text-gray-900'" class="px-2.5 py-1.5 rounded transition-all">Keluar</button>
                        <button @click="setFilter('type', 'internal')" :class="filterType === 'internal' ? 'bg-white text-primary shadow-sm' : 'hover:text-gray-900'" class="px-2.5 py-1.5 rounded transition-all">Internal</button>
                    </div>

                    <!-- Status Filter Group -->
                    <div class="flex bg-gray-100 p-0.5 rounded-lg text-[10px] font-bold text-gray-600">
                        <button @click="filterStatus = 'all'" :class="filterStatus === 'all' ? 'bg-white text-primary shadow-sm' : 'hover:text-gray-900'" class="px-2.5 py-1.5 rounded transition-all">Semua Status</button>
                        <button @click="setFilter('status', 'pending')" :class="filterStatus === 'pending' ? 'bg-white text-primary shadow-sm' : 'hover:text-gray-900'" class="px-2.5 py-1.5 rounded transition-all">Pending</button>
                        <button @click="setFilter('status', 'in_progress')" :class="filterStatus === 'in_progress' ? 'bg-white text-primary shadow-sm' : 'hover:text-gray-900'" class="px-2.5 py-1.5 rounded transition-all">Proses</button>
                        <button @click="setFilter('status', 'completed')" :class="filterStatus === 'completed' ? 'bg-white text-primary shadow-sm' : 'hover:text-gray-900'" class="px-2.5 py-1.5 rounded transition-all">Selesai</button>
                        <button @click="setFilter('status', 'archived')" :class="filterStatus === 'archived' ? 'bg-white text-primary shadow-sm' : 'hover:text-gray-900'" class="px-2.5 py-1.5 rounded transition-all">Arsip</button>
                    </div>

                    <!-- Clear Filters -->
                    <button x-show="filterType !== 'all' || filterStatus !== 'all' || searchQuery !== ''" 
                            @click="resetFilters()" 
                            class="text-[10px] text-red-600 hover:text-red-800 font-bold flex items-center gap-1 px-2 py-1 rounded bg-red-50 hover:bg-red-100 transition-colors">
                        <i class="ph ph-trash-simple"></i> Reset
                    </button>
                </div>
            </div>

            <!-- Mail Table Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-white">
                    <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                        <i class="ph ph-envelope-simple text-primary text-lg"></i> Surat Terbaru
                    </h3>
                    <span class="text-xs text-gray-500 font-medium" x-text="`Menampilkan ${filteredMails.length} surat`"></span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-full">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/50">
                                <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">No. Ref & Perihal</th>
                                <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Tipe</th>
                                <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Pengirim / Penerima</th>
                                <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <!-- Alpine Rendered Rows -->
                            <template x-for="mail in filteredMails" :key="mail.id">
                                <tr @click="selectMail(mail)" 
                                    :class="selectedMail && selectedMail.id === mail.id ? 'bg-emerald-50/30' : ''"
                                    class="hover:bg-gray-50/80 cursor-pointer transition-all duration-150">
                                    <td class="px-6 py-4">
                                        <div class="text-xs font-extrabold text-primary hover:text-emerald-700 transition-colors mb-0.5" x-text="mail.reference_number"></div>
                                        <div class="text-xs font-semibold text-gray-700 line-clamp-1" :title="mail.subject" x-text="mail.subject"></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-700 border border-gray-200" x-text="mail.type_label"></span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-600">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-800 truncate max-w-[150px]" x-text="mail.sender_name"></span>
                                            <span class="text-[10px] text-gray-400 truncate max-w-[150px]" x-text="mail.recipient_name"></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="`inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border border-${mail.status_color}-200 bg-${mail.status_color}-50/50 text-${mail.status_color}-700`" x-text="mail.status_label"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 font-medium" x-text="mail.tanggal_surat_formatted"></td>
                                </tr>
                            </template>

                            <!-- Empty State -->
                            <tr x-show="filteredMails.length === 0">
                                <td colspan="5" class="p-12 text-center text-gray-400 bg-white">
                                    <i class="ph ph-envelope-open text-4xl mb-3 opacity-40 block mx-auto text-primary"></i>
                                    <p class="text-xs font-semibold text-gray-600">Belum ada surat terbaru atau tidak ada data yang cocok.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Native Monthly Summary Chart Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                        <i class="ph ph-chart-bar text-primary text-xl"></i> Ringkasan Bulanan (6 Bulan Terakhir)
                    </h3>
                    
                    <!-- Chart Legend -->
                    <div class="flex flex-wrap items-center gap-4 text-[10px] font-bold text-gray-500">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-sm bg-primary block"></span> Surat Masuk
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-sm bg-secondary block"></span> Surat Keluar
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-sm bg-amber-500 block"></span> Surat Internal
                        </div>
                    </div>
                </div>

                <!-- Calculate max stats value for scaling -->
                @php
                    $maxVal = 1;
                    foreach($monthlyStats as $stat) {
                        $maxVal = max($maxVal, $stat['incoming'], $stat['outgoing'], $stat['internal']);
                    }
                @endphp

                <!-- Graph Grid Container -->
                <div class="flex items-end justify-between gap-3 h-48 pt-4 border-b border-gray-100 relative">
                    <!-- Y-Axis helpers -->
                    <div class="absolute inset-x-0 top-4 border-t border-gray-100/50 pointer-events-none"></div>
                    <div class="absolute inset-x-0 top-1/2 -translate-y-1/2 border-t border-gray-100/50 pointer-events-none"></div>
                    
                    @foreach($monthlyStats as $key => $stat)
                        <div class="flex-1 flex flex-col items-center group relative h-full justify-end">
                            
                            <!-- Columns Container -->
                            <div class="w-full flex items-end justify-center gap-1 h-36">
                                
                                <!-- Incoming Bar -->
                                <div class="flex-1 max-w-[12px] bg-primary rounded-t-sm transition-all duration-300 hover:brightness-95 relative group/bar"
                                     style="height: {{ $stat['incoming'] > 0 ? max(4, ($stat['incoming'] / $maxVal) * 100) : 0 }}%">
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 bg-gray-900 text-white text-[9px] font-bold py-1 px-1.5 rounded opacity-0 pointer-events-none group-hover/bar:opacity-100 transition-opacity whitespace-nowrap z-30 shadow-md">
                                        Masuk: {{ $stat['incoming'] }}
                                    </div>
                                </div>

                                <!-- Outgoing Bar -->
                                <div class="flex-1 max-w-[12px] bg-secondary rounded-t-sm transition-all duration-300 hover:brightness-95 relative group/bar"
                                     style="height: {{ $stat['outgoing'] > 0 ? max(4, ($stat['outgoing'] / $maxVal) * 100) : 0 }}%">
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 bg-gray-900 text-white text-[9px] font-bold py-1 px-1.5 rounded opacity-0 pointer-events-none group-hover/bar:opacity-100 transition-opacity whitespace-nowrap z-30 shadow-md">
                                        Keluar: {{ $stat['outgoing'] }}
                                    </div>
                                </div>

                                <!-- Internal Bar -->
                                <div class="flex-1 max-w-[12px] bg-amber-500 rounded-t-sm transition-all duration-300 hover:brightness-95 relative group/bar"
                                     style="height: {{ $stat['internal'] > 0 ? max(4, ($stat['internal'] / $maxVal) * 100) : 0 }}%">
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 bg-gray-900 text-white text-[9px] font-bold py-1 px-1.5 rounded opacity-0 pointer-events-none group-hover/bar:opacity-100 transition-opacity whitespace-nowrap z-30 shadow-md">
                                        Internal: {{ $stat['internal'] }}
                                    </div>
                                </div>

                            </div>
                            
                            <!-- Label -->
                            <div class="text-[10px] font-bold text-gray-500 mt-2 whitespace-nowrap">{{ $stat['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        <!-- Right Side: Tasks & Active Dispositions -->
        <div class="lg:col-span-1 space-y-6">
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden h-full flex flex-col">
                <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-white">
                    <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                        <i class="ph ph-git-fork text-primary text-lg"></i> Tugas Disposisi Anda
                    </h3>
                </div>
                
                <div class="p-6 flex-1 bg-gray-50/20">
                    @if(isset($myDispositions) && $myDispositions->isEmpty())
                        <div class="py-12 text-center text-gray-400">
                            <i class="ph ph-check-circle text-4xl mb-3 opacity-40 text-primary block mx-auto"></i>
                            <p class="text-xs font-semibold text-gray-600">Tidak ada tugas disposisi baru.</p>
                            <p class="text-[10px] text-gray-400 mt-1">Kerja bagus, semua tugas telah selesai!</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($myDispositions ?? [] as $disp)
                            <a href="{{ route('dispositions.show', $disp) }}" class="block group">
                                <div class="bg-white border border-gray-200 rounded-lg p-4 transition-all duration-200 hover:border-primary hover:shadow-sm">
                                    <div class="flex justify-between items-start mb-2 gap-3">
                                        <h4 class="text-xs font-bold text-gray-800 group-hover:text-primary transition-colors line-clamp-2 leading-relaxed">
                                            {{ $disp->mail->subject }}
                                        </h4>
                                        @if($disp->isOverdue())
                                            <span class="inline-flex shrink-0 items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-red-100 text-red-700">
                                                Overdue
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-[10px] text-gray-500 flex items-center gap-1.5 mb-3">
                                        <i class="ph ph-user text-gray-400"></i> Dari: <span class="font-bold text-gray-700">{{ $disp->fromUser->name }}</span>
                                    </div>
                                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                                        <div class="text-[9px] font-bold text-primary bg-primary/10 px-2 py-0.5 rounded border border-primary/20">
                                            {{ $disp->action_label }}
                                        </div>
                                        @if($disp->due_date)
                                            <div class="text-[10px] text-gray-400 flex items-center gap-1 font-medium">
                                                <i class="ph ph-calendar-blank"></i> B.W: {{ $disp->due_date->format('d/m/y') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                        
                        <a href="{{ route('dispositions.index') }}" class="mt-6 w-full flex items-center justify-center gap-2 bg-white border border-gray-200 hover:bg-gray-50 hover:text-primary text-gray-600 font-bold py-2.5 px-4 rounded-lg transition duration-200 text-xs shadow-sm">
                            Lihat Semua Tugas <i class="ph ph-arrow-right"></i>
                        </a>
                    @endif
                </div>
            </div>

        </div>

    </div>

    <!-- Drawer Side Panel Backdrop -->
    <div x-show="selectedMail" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[60]" 
         @click="selectedMail = null"
         style="display: none;"></div>

    <!-- Drawer Slide-over Panel -->
    <div x-show="selectedMail" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed right-0 top-0 h-full w-full max-w-md bg-white border-l border-gray-200 shadow-2xl z-[70] flex flex-col" 
         @keydown.escape.window="selectedMail = null"
         style="display: none;">
        
        <!-- Drawer Header -->
        <div class="h-16 border-b border-gray-150 px-6 flex items-center justify-between shrink-0 bg-gray-50">
            <div>
                <h4 class="font-extrabold text-gray-800 text-xs sm:text-sm tracking-tight" x-text="selectedMail ? selectedMail.reference_number : ''"></h4>
                <p class="text-[9px] text-primary font-bold uppercase tracking-wider mt-0.5" x-text="selectedMail ? selectedMail.type_label : ''"></p>
            </div>
            <button @click="selectedMail = null" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-200 transition-colors">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <!-- Drawer Body -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            
            <!-- Quick Status Badges -->
            <div class="flex flex-wrap gap-2" x-show="selectedMail">
                <span :class="`inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold border border-${selectedMail ? selectedMail.status_color : 'gray'}-200 bg-${selectedMail ? selectedMail.status_color : 'gray'}-50 text-${selectedMail ? selectedMail.status_color : 'gray'}-700`" x-text="selectedMail ? selectedMail.status_label : ''"></span>
                <span :class="`inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold border border-${selectedMail ? selectedMail.priority_color : 'gray'}-200 bg-${selectedMail ? selectedMail.priority_color : 'gray'}-50 text-${selectedMail ? selectedMail.priority_color : 'gray'}-700`" x-text="selectedMail ? selectedMail.priority_label : ''"></span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold border border-blue-200 bg-blue-50 text-blue-700 uppercase" x-text="selectedMail ? selectedMail.classification : ''"></span>
            </div>

            <!-- Detail Fields -->
            <div class="space-y-4 text-xs" x-show="selectedMail">
                
                <!-- Perihal / Subject -->
                <div>
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Perihal / Subjek</span>
                    <p class="font-bold text-gray-800 text-sm mt-1 leading-relaxed" x-text="selectedMail ? selectedMail.subject : ''"></p>
                </div>
                
                <!-- Date Grid -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tanggal Surat</span>
                        <p class="font-semibold text-gray-700 mt-1" x-text="selectedMail ? selectedMail.tanggal_surat_formatted : ''"></p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tanggal Diterima</span>
                        <p class="font-semibold text-gray-700 mt-1" x-text="selectedMail ? (selectedMail.received_date_formatted || '-') : ''"></p>
                    </div>
                </div>

                <!-- Sender vs Recipient Info -->
                <div class="grid grid-cols-2 gap-4 border-t border-gray-100 pt-4">
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Pengirim</span>
                        <p class="font-bold text-gray-800 mt-1" x-text="selectedMail ? selectedMail.sender_name : ''"></p>
                        <p class="text-[10px] text-gray-500 mt-0.5" x-text="selectedMail ? selectedMail.sender_organization || '-' : ''"></p>
                        <p class="text-[10px] text-gray-400 mt-0.5 truncate" x-text="selectedMail ? selectedMail.sender_email || '-' : ''"></p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Penerima</span>
                        <p class="font-bold text-gray-800 mt-1" x-text="selectedMail ? selectedMail.recipient_name : ''"></p>
                        <p class="text-[10px] text-gray-500 mt-0.5" x-text="selectedMail ? selectedMail.recipient_department || '-' : ''"></p>
                        <p class="text-[10px] text-gray-400 mt-0.5 truncate" x-text="selectedMail ? selectedMail.recipient_email || '-' : ''"></p>
                    </div>
                </div>

                <!-- Unit & Category classification -->
                <div class="border-t border-gray-100 pt-4">
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Unit Kerja & Jenis Dokumen</span>
                    <p class="font-semibold text-gray-700 mt-1" x-text="selectedMail ? `${selectedMail.unit_name} — ${selectedMail.document_type_name}` : ''"></p>
                </div>

                <!-- Body / Description -->
                <div class="border-t border-gray-100 pt-4">
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Uraian / Ringkasan Isi</span>
                    <div class="mt-2 text-gray-700 bg-gray-50 border border-gray-150 p-3 rounded-lg leading-relaxed whitespace-pre-line text-xs" x-text="selectedMail ? selectedMail.body : ''"></div>
                </div>

                <!-- Document Attachment (If exists) -->
                <div class="border-t border-gray-100 pt-4" x-show="selectedMail && selectedMail.attachment_path">
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Berkas Lampiran</span>
                    <div class="mt-2 flex items-center justify-between bg-emerald-50/50 border border-emerald-100 p-3 rounded-lg">
                        <div class="flex items-center gap-2 min-w-0">
                            <i class="ph ph-file-pdf text-primary text-2xl shrink-0"></i>
                            <span class="text-xs font-bold text-emerald-950 truncate" x-text="selectedMail ? selectedMail.attachment_name : ''"></span>
                        </div>
                        <a :href="selectedMail ? selectedMail.attachment_path : '#'" target="_blank" class="btn btn-primary btn-sm flex items-center gap-1 font-bold shadow-none !bg-primary hover:!bg-emerald-800 shrink-0 text-[10px]">
                            <i class="ph ph-download-simple"></i> Buka Lampiran
                        </a>
                    </div>
                </div>

                <!-- Dispositions History Timeline -->
                <div class="border-t border-gray-100 pt-4" x-show="selectedMail && selectedMail.dispositions && selectedMail.dispositions.length > 0">
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Riwayat Alur Disposisi</span>
                    <div class="space-y-3">
                        <template x-for="disp in (selectedMail ? selectedMail.dispositions : [])" :key="disp.id">
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3.5 space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-gray-800 text-xs" x-text="`Ke: ${disp.to_user_name}`"></span>
                                    <span :class="`px-1.5 py-0.5 rounded text-[9px] font-bold border border-${disp.status_color}-200 bg-${disp.status_color}-50 text-${disp.status_color}-700`" x-text="disp.status_label"></span>
                                </div>
                                <p class="text-[10px] text-gray-500 font-semibold" x-text="`Dari: ${disp.from_user_name} | Hal: ${disp.action_label}`"></p>
                                <div class="text-gray-700 bg-white p-2.5 rounded border border-gray-150 text-xs leading-relaxed" x-text="disp.instruction"></div>
                                
                                <!-- Tanggapan/Response notes -->
                                <div x-show="disp.response_notes" class="mt-2 bg-emerald-50 border border-emerald-100 p-2.5 rounded text-xs text-emerald-950">
                                    <span class="font-bold block text-primary text-[10px] uppercase tracking-wider mb-1">Tanggapan Balasan</span>
                                    <span class="block leading-relaxed whitespace-pre-line" x-text="disp.response_notes"></span>
                                    <span class="block text-[9px] text-gray-400 mt-2 font-medium" x-text="`Dibalas pada: ${disp.responded_at_formatted}`"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

            <!-- Disposition Widget (Toggled Open via footer action) -->
            <div x-show="dispositionFormOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="bg-emerald-50/50 border border-primary/20 rounded-xl p-4 space-y-4"
                 style="display: none;">
                
                <div class="flex justify-between items-center border-b border-primary/10 pb-2">
                    <h5 class="font-bold text-primary text-xs flex items-center gap-1.5">
                        <i class="ph ph-git-branch text-sm"></i> Buat Instruksi Disposisi
                    </h5>
                    <button type="button" @click="dispositionFormOpen = false" class="text-gray-400 hover:text-gray-600 text-[10px] font-bold uppercase">Batal</button>
                </div>
                
                <!-- Disposition Submission Form -->
                <form action="{{ route('dispositions.store') }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <input type="hidden" name="mail_id" :value="selectedMail ? selectedMail.id : ''">

                    <!-- To User dropdown -->
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Tujuan Disposisi (Pegawai)</label>
                        <select name="to_user_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent bg-white text-gray-700">
                            <option value="">-- Pilih Penerima --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->position }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Action Type & Due Date grid -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Hal Tindakan</label>
                            <select name="action_type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent bg-white text-gray-700">
                                <option value="for_action">Tindaklanjuti</option>
                                <option value="for_review">Telaah</option>
                                <option value="for_information">Ketahui</option>
                                <option value="for_approval">Setujui</option>
                                <option value="for_filing">Arsipkan</option>
                                <option value="for_reply">Balas</option>
                                <option value="coordinate">Koordinasikan</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Batas Waktu</label>
                            <input type="date" name="due_date" min="{{ date('Y-m-d', strtotime('+1 day')) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent bg-white text-gray-700">
                        </div>
                    </div>

                    <!-- Instructions text area -->
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Instruksi Penyelesaian</label>
                        <textarea name="instruction" required rows="3" placeholder="Tuliskan petunjuk atau catatan penyelesaian..." class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent bg-white text-gray-700"></textarea>
                    </div>

                    <!-- Send button -->
                    <button type="submit" class="w-full btn btn-primary flex items-center justify-center gap-1.5 font-bold shadow-none !bg-primary hover:!bg-emerald-800 text-white rounded-lg py-2.5">
                        <i class="ph ph-paper-plane-right"></i> Kirim Disposisi
                    </button>
                </form>
            </div>

        </div>

        <!-- Drawer Footer Panel Actions -->
        <div class="h-20 border-t border-gray-100 px-6 flex items-center gap-3 shrink-0 bg-gray-50" x-show="selectedMail">
            
            <!-- Toggle Disposition Widget button -->
            <button @click="dispositionFormOpen = !dispositionFormOpen" class="flex-1 btn btn-secondary flex items-center justify-center gap-1.5 font-bold shadow-none py-2.5 border border-gray-300 hover:bg-gray-150 text-gray-700 rounded-lg text-xs">
                <i class="ph ph-git-branch text-base"></i> Disposisi
            </button>

            <!-- Complete Action Form button (if authorized) -->
            <div class="flex-1" x-show="selectedMail && selectedMail.can_update_status && selectedMail.status !== 'completed' && selectedMail.status !== 'archived'">
                <form :action="'/surat/' + selectedMail.id + '/status'" method="POST" class="w-full">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="w-full btn btn-success flex items-center justify-center gap-1.5 font-bold shadow-none py-2.5 !bg-green-600 hover:!bg-green-700 text-white rounded-lg text-xs">
                        <i class="ph ph-check-circle text-base"></i> Selesai
                    </button>
                </form>
            </div>

            <!-- Archive Action Form button (only for Manager/Admin) -->
            <div class="flex-1" x-show="selectedMail && selectedMail.can_archive && selectedMail.status !== 'archived'">
                <form :action="'/surat/' + selectedMail.id + '/status'" method="POST" class="w-full">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="archived">
                    <button type="submit" class="w-full btn btn-warning flex items-center justify-center gap-1.5 font-bold shadow-none py-2.5 !bg-amber-600 hover:!bg-amber-700 text-white rounded-lg text-xs">
                        <i class="ph ph-archive text-base"></i> Arsipkan
                    </button>
                </form>
            </div>

        </div>

    </div>

</div>

@endsection
