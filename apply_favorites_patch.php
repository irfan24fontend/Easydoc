<?php
/*
 * Favorite Medicines: two tiny edits to existing files.
 * Run once from the project root (D:\easydoc):   php apply_favorites_patch.php
 *
 *  1) resources/views/doctor/prescriptions/create.blade.php
 *       - search dropdown shows the company name + a "your brand" badge
 *       - loads the "Add automatically" medicines into every new prescription
 *  2) routes/web.php
 *       - adds: require __DIR__.'/doctor_favorites.php';
 *
 * A .bak copy of each file is saved next to it. Running it twice is safe.
 */

function readNormalized(string $file): array
{
    $raw = file_get_contents($file);
    $crlf = strpos($raw, "\r\n") !== false;

    return [str_replace("\r\n", "\n", $raw), $crlf];
}

function writeBack(string $file, string $text, bool $crlf): void
{
    copy($file, $file . '.bak');
    file_put_contents($file, $crlf ? str_replace("\n", "\r\n", $text) : $text);
}

/* ---------------- 1) prescription create view ---------------- */
$view = 'resources/views/doctor/prescriptions/create.blade.php';

if (! is_file($view)) {
    echo "NOT FOUND: $view  (run this from the project root)\n";
    exit(1);
}

[$text, $crlf] = readNormalized($view);
$original = $text;

$a = <<<'EOT'
<div class="text-[11px] text-slate-400">${escHtml(item.generic_name || '')}</div>
EOT;
$aNew = <<<'EOT'
<div class="text-[11px] text-slate-400">${escHtml(item.generic_name || '')}${item.company ? ' · ' + escHtml(item.company) : ''}</div>
EOT;

$b = <<<'EOT'
${item.is_sponsored ? '<span class="text-[10px] font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full whitespace-nowrap"><i class="fa-solid fa-star"></i> Sponsored</span>' : ''}
EOT;
$bNew = <<<'EOT'
${item.is_favorite_company ? '<span class="text-[10px] font-bold px-2 py-0.5 rounded-full whitespace-nowrap" style="background:#d1fae5;color:#065f46"><i class="fa-solid fa-star"></i> আপনার ব্র্যান্ড</span>' : ''}
                                ${item.is_sponsored ? '<span class="text-[10px] font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full whitespace-nowrap"><i class="fa-solid fa-star"></i> Sponsored</span>' : ''}
EOT;

if (strpos($text, 'is_favorite_company') === false) {
    $text = str_replace($a, $aNew, $text, $c1);
    $text = str_replace($b, $bNew, $text, $c2);
    echo ($c1 === 1 && $c2 === 1)
        ? "OK    search dropdown patched\n"
        : "WARN  search dropdown: expected 1 match each, found $c1 and $c2 (skipped those that did not match)\n";
} else {
    echo "SKIP  search dropdown already patched\n";
}

if (strpos($text, '_default-medicines') === false) {
    $text = rtrim($text, "\n") . "\n\n@push('scripts')\n@include('doctor.prescriptions._default-medicines')\n@endpush\n";
    echo "OK    default medicines include added\n";
} else {
    echo "SKIP  default medicines include already there\n";
}

if ($text !== $original) {
    writeBack($view, $text, $crlf);
}

/* ---------------- 2) routes/web.php ---------------- */
$routes = 'routes/web.php';

if (is_file($routes)) {
    [$rt, $rcrlf] = readNormalized($routes);

    if (strpos($rt, 'doctor_favorites.php') === false) {
        $rt = rtrim($rt, "\n") . "\n\nrequire __DIR__.'/doctor_favorites.php';\n";
        writeBack($routes, $rt, $rcrlf);
        echo "OK    routes/web.php: favorites routes included\n";
    } else {
        echo "SKIP  routes/web.php already includes favorites routes\n";
    }
} else {
    echo "NOT FOUND: $routes\n";
}

echo "\nDone. Now run:  php artisan migrate   and   php artisan route:list --name=favorites\n";
