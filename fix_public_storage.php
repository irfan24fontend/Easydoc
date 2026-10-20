<?php
/**
 * EasyDoc: image-gulo deploy (git pull) e bhangbe na, ei fix.
 *
 * LOCAL project root e (jekhane `artisan` ache) chalan, tarpor commit + push korun:
 *     php fix_public_storage.php
 *
 * Ki kore:
 *  1) routes/web.php te `/storage/{path}` route add kore. Image ekhon Laravel diye serve hoy,
 *     tai public_html/storage folder thaklo ki gelo, kichu jay ashe na.
 *  2) config/filesystems.php:
 *       - 'serve' => true ke false kore (framework er default `storage/{path}` route er sathe conflict thamate)
 *       - public disk root theke PUBLIC_DISK_ROOT hotiye default `storage_path('app/public')` kore
 *  Backup: *.bak-storage. Dobar chalale kichu kore na.
 */

if (! file_exists(__DIR__ . '/artisan')) {
    fwrite(STDERR, "ERROR: `artisan` file paoa jayni. Laravel project root e chalan.\n");
    exit(1);
}

$root = __DIR__;

function bak(string $path): void
{
    if (! file_exists($path . '.bak-storage')) {
        copy($path, $path . '.bak-storage');
    }
}

// ---------------------------------------------------------------- config
echo "== config/filesystems.php ==\n";
$cfgPath = $root . '/config/filesystems.php';
$cfg = file_get_contents($cfgPath);
$orig = $cfg;

$cfg = preg_replace(
    '/env\(\s*[\'"]PUBLIC_DISK_ROOT[\'"]\s*,\s*storage_path\(\s*[\'"]app\/public[\'"]\s*\)\s*\)/',
    "storage_path('app/public')",
    $cfg,
    -1,
    $c1
);
$cfg = preg_replace('/([\'"]serve[\'"]\s*=>\s*)true/', '$1false', $cfg, -1, $c2);

if ($cfg !== $orig) {
    bak($cfgPath);
    file_put_contents($cfgPath, $cfg);
    echo "  updated (PUBLIC_DISK_ROOT hotano: $c1, serve=false: $c2)\n";
} else {
    echo "  no change (already ok)\n";
}

// ---------------------------------------------------------------- route
echo "\n== routes/web.php ==\n";
$routesPath = $root . '/routes/web.php';
$routes = file_get_contents($routesPath);

if (str_contains($routes, 'public.storage')) {
    echo "  skip   route already ache\n";
} else {
    bak($routesPath);

    $block = <<<'ROUTE'


// Public disk files (doctor photo, chamber pad image) Laravel diye serve hoy.
// Deploy e public_html/storage muche gelew image bhangbe na.
Route::get('/storage/{path}', function (string $path) {
    $base = realpath(storage_path('app/public'));
    $file = $base ? realpath($base . DIRECTORY_SEPARATOR . $path) : false;

    abort_unless(
        $file && is_file($file) && str_starts_with($file, $base . DIRECTORY_SEPARATOR),
        404
    );

    return response()->file($file, ['Cache-Control' => 'public, max-age=604800']);
})->where('path', '.*')->name('public.storage');
ROUTE;

    file_put_contents($routesPath, rtrim($routes) . "\n" . $block . "\n");
    echo "  added  /storage/{path} route\n";
}

// ---------------------------------------------------------------- lint
echo "\n== Syntax check ==\n";
foreach (['config/filesystems.php', 'routes/web.php'] as $rel) {
    $out = [];
    $code = 0;
    exec('php -l ' . escapeshellarg($root . '/' . $rel) . ' 2>&1', $out, $code);
    echo ($code === 0 ? '  ok     ' : '  ERROR  ') . $rel . ($code === 0 ? '' : ' ' . implode(' ', $out)) . "\n";
}

echo "\nEkhon: git add -A && git commit -m \"Serve public storage via route\" && git push\n";
