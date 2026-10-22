<?php
// ===== Doctor\PrescriptionController::print() e, "$replacements = [" er THIK AGE boshan =====
$fuRaw = $prescription->follow_up_date
    ?? $prescription->followup_date
    ?? $prescription->next_visit_date
    ?? $prescription->next_visit
    ?? null;

$fuText = '';
if (!empty($fuRaw)) {
    try { $fuText = \Illuminate\Support\Carbon::parse($fuRaw)->format('d M Y'); }
    catch (\Throwable $e) { $fuText = (string) $fuRaw; }
}
$followupBlock = $fuText === ''
    ? ''
    : '<div class="rx-followup">Follow-up: ' . e($fuText) . ' <small>(abar dekha korun)</small></div>';

// ===== Ar $replacements array er bhitore ei 2 ta line add korun =====
//     'followup_block' => $followupBlock,
//     'followup_date'  => $fuText,
