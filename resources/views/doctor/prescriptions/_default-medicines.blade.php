{{--
    Adds the doctor's "Add automatically" favorite medicines to every NEW prescription.
    Included once at the end of doctor/prescriptions/create.blade.php (inside @push('scripts')).
    Needs the globals defined by that page: appendMedicineRow, prescriptionList, OLD_MEDS,
    medRowCount, currentMode, setMode, clearVisit.
--}}
@php
    $__favDoctor   = auth()->user()?->doctor;
    $__favDefaults = $__favDoctor ? app(\App\Services\FavoriteMedicineService::class)->defaultsFor($__favDoctor) : [];
@endphp

@if (count($__favDefaults))
<script>
(function () {
    var DEFAULT_MEDS = @json($__favDefaults);

    if (typeof appendMedicineRow !== 'function' || typeof prescriptionList === 'undefined') return;

    var appliedOnce = false;

    function applyDefaults(force) {
        // Only into an empty list, so nothing the doctor already wrote is touched
        if (typeof medRowCount === 'function' && medRowCount() > 0) return;
        if (!force && appliedOnce) return;
        if (typeof currentMode === 'function' && currentMode() !== 'full') return;

        DEFAULT_MEDS.forEach(function (m) {
            appendMedicineRow({
                id: m.id, name: m.name, type: m.type, generic: m.generic,
                dose: m.doseText ? '' : m.dose, doseText: m.doseText || '',
                duration: m.duration, instruction: m.instruction,
            });

            // Small "default" badge so the doctor can see which rows came automatically
            var row = prescriptionList.lastElementChild;
            if (row) {
                row.dataset.autoDefault = '1';
                var first = row.querySelector('td');
                if (first) {
                    var tag = document.createElement('span');
                    tag.textContent = 'ডিফল্ট';
                    tag.title = 'Favorite Medicines থেকে স্বয়ংক্রিয়ভাবে যোগ হয়েছে';
                    tag.style.cssText = 'margin-left:6px;font-size:10px;font-weight:700;background:#e0e7ff;color:#3730a3;padding:1px 7px;border-radius:999px;vertical-align:middle;';
                    first.insertBefore(tag, first.querySelector('input'));
                }
            }
        });
        appliedOnce = true;
    }

    // New patient started from the queue: the visit form is cleared, so load the defaults again
    if (typeof clearVisit === 'function') {
        var originalClear = clearVisit;
        window.clearVisit = function () {
            originalClear.apply(this, arguments);
            applyDefaults(true);
        };
    }

    // Page opened in "Investigation Advice" mode and later switched to a full prescription
    if (typeof setMode === 'function') {
        var originalSetMode = setMode;
        window.setMode = function (mode) {
            originalSetMode.apply(this, arguments);
            if (mode === 'full') applyDefaults(false);
        };
    }

    // First load (not after a validation error, where the doctor's own rows come back)
    var hadOldRows = (typeof OLD_MEDS !== 'undefined') && OLD_MEDS && OLD_MEDS.length > 0;
    if (!hadOldRows) applyDefaults(false);
})();
</script>
@endif
