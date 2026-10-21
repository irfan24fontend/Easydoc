<?php
/*
 * Wallet recharge + notifications: one tiny edit to an existing file.
 * Run once from the project root (D:\easydoc):   php apply_wallet_patch.php
 *
 *   routes/web.php  ->  adds:  require __DIR__.'/wallet_notifications.php';
 *
 * A .bak copy of the file is saved next to it. Running it twice is safe.
 */

$routes = 'routes/web.php';

if (! is_file($routes)) {
    echo "NOT FOUND: $routes  (run this from the project root)\n";
    exit(1);
}

$raw = file_get_contents($routes);
$crlf = strpos($raw, "\r\n") !== false;
$text = str_replace("\r\n", "\n", $raw);

if (strpos($text, 'wallet_notifications.php') !== false) {
    echo "SKIP  routes/web.php already includes the wallet and notification routes\n";
} else {
    $text = rtrim($text, "\n") . "\n\nrequire __DIR__.'/wallet_notifications.php';\n";
    copy($routes, $routes . '.bak');
    file_put_contents($routes, $crlf ? str_replace("\n", "\r\n", $text) : $text);
    echo "OK    routes/web.php: wallet and notification routes included\n";
}

echo "\nNow run:  php artisan migrate\n";
echo "Then check:  php artisan route:list --name=wallet\n";
