{{-- Unit Assignment Checkbox Group --}}
{{-- Usage: @include('partials.unit-checkboxes', ['units' => $units, 'selectedIds' => $selectedIds]) --}}
@php
    $selectedIds = $selectedIds ?? collect();
    if (!$selectedIds instanceof \Illuminate\Support\Collection) {
        $selectedIds = collect($selectedIds);
    }
@endphp

<div class="form-group">
    <label class="form-label">
        Unit/Bidang yang Ditugaskan
        <span class="req">*</span>
        <span class="text-xs font-normal text-gray-400 ml-1">(Pilih 1–3 unit)</span>
    </label>
    
    @error('units')
        <div class="text-xs text-red-600 mb-2 flex items-center gap-1">
            <i class="ph ph-warning-circle"></i> {{ $message }}
        </div>
    @enderror
    
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
        @foreach($units as $unit)
        @php $isChecked = $selectedIds->contains($unit->id); @endphp
        <label for="unit_{{ $unit->id }}"
            class="flex items-start gap-3 p-3 rounded-lg border-2 cursor-pointer transition-all duration-150
                   {{ $isChecked ? 'border-secondary bg-secondary/10' : 'border-gray-200 hover:border-secondary/50 hover:bg-gray-50' }}
                   unit-checkbox-label">
            <input type="checkbox"
                   id="unit_{{ $unit->id }}"
                   name="units[]"
                   value="{{ $unit->id }}"
                   class="unit-checkbox mt-0.5 w-4 h-4 accent-secondary rounded"
                   {{ $isChecked ? 'checked' : '' }}>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-semibold text-gray-800 truncate">{{ $unit->name }}</div>
                @if($unit->description)
                    <div class="text-xs text-gray-400 truncate">{{ $unit->description }}</div>
                @endif
            </div>
        </label>
        @endforeach
    </div>
    <p class="text-xs text-gray-400 mt-1">
        <span id="unit-count-msg" class="font-medium text-gray-500">0 unit dipilih.</span>
        Maksimal 3 unit per pengguna sesuai kebijakan institusi.
    </p>
</div>

<script>
(function () {
    const checkboxes = document.querySelectorAll('.unit-checkbox');
    const countMsg   = document.getElementById('unit-count-msg');

    function update() {
        let count = 0;
        checkboxes.forEach(cb => {
            const label = cb.closest('.unit-checkbox-label');
            if (cb.checked) {
                count++;
                label.classList.add('border-secondary', 'bg-secondary/10');
                label.classList.remove('border-gray-200');
            } else {
                label.classList.remove('border-secondary', 'bg-secondary/10');
                label.classList.add('border-gray-200');
            }
        });
        countMsg.textContent = count + ' unit dipilih.';
        countMsg.className   = count > 3 ? 'font-medium text-red-500' : 'font-medium text-gray-500';

        // Disable unchecked if limit reached
        checkboxes.forEach(cb => {
            if (!cb.checked && count >= 3) {
                cb.disabled = true;
                cb.closest('.unit-checkbox-label').classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                cb.disabled = false;
                cb.closest('.unit-checkbox-label').classList.remove('opacity-50', 'cursor-not-allowed');
            }
        });
    }

    checkboxes.forEach(cb => cb.addEventListener('change', update));
    update(); // Run on load
})();
</script>
