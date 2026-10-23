<?php
/**
 * EasyDoc: Brevo API mail transport installer
 *
 * Project root e (jekhane `artisan` ache) rekhe chalan:
 *     php install_brevo_mail.php
 *
 * Ki kore:
 *  - app/Services/Mail/BrevoApiTransport.php  (Brevo-r HTTP API diye mail pathay, SMTP port lage na)
 *  - app/Providers/BrevoMailServiceProvider.php
 *  - bootstrap/providers.php te provider register kore
 *  - config/services.php te 'brevo' => ['key' => env('BREVO_API_KEY')] add kore
 *  - config/mail.php te 'brevo-api' mailer add kore (na thakle)
 *  Dobar chalale duplicate kore na. Backup *.bak-brevo.
 */

if (! file_exists(__DIR__ . '/artisan')) {
    fwrite(STDERR, "ERROR: `artisan` file paoa jayni. Laravel project root e rekhe chalan.\n");
    exit(1);
}

$root  = __DIR__;
$files = [];

$files['app/Services/Mail/BrevoApiTransport.php'] = <<<'BREVO_EOF'
<?php

namespace App\Services\Mail;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

/**
 * Brevo (ex-Sendinblue) er HTTP API diye mail pathay.
 * SMTP port (25/465/587) lage na, tai hosting provider SMTP block korle o kaj kore.
 * API docs: https://developers.brevo.com/reference/sendtransacemail
 */
class BrevoApiTransport extends AbstractTransport
{
    public function __construct(private string $apiKey)
    {
        parent::__construct();
    }

    public function __toString(): string
    {
        return 'brevo+api';
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $from = $email->getFrom()[0] ?? null;

        $payload = [
            'sender'      => $from ? $this->addr($from) : null,
            'to'          => array_map($this->addr(...), $email->getTo()),
            'subject'     => (string) $email->getSubject(),
            'htmlContent' => $email->getHtmlBody() ?: null,
            'textContent' => $email->getTextBody() ?: null,
        ];

        if ($cc = $email->getCc()) {
            $payload['cc'] = array_map($this->addr(...), $cc);
        }
        if ($bcc = $email->getBcc()) {
            $payload['bcc'] = array_map($this->addr(...), $bcc);
        }
        if ($replyTo = $email->getReplyTo()) {
            $payload['replyTo'] = $this->addr($replyTo[0]);
        }

        $payload = array_filter($payload, fn ($v) => $v !== null && $v !== []);

        $response = Http::withHeaders([
            'api-key'      => $this->apiKey,
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', $payload);

        if ($response->failed()) {
            throw new TransportException(
                'Brevo API email pathano gelo na: ' . $response->status() . ' ' . $response->body()
            );
        }
    }

    private function addr(Address $address): array
    {
        return array_filter([
            'email' => $address->getAddress(),
            'name'  => $address->getName() ?: null,
        ]);
    }
}
BREVO_EOF;

$files['app/Providers/BrevoMailServiceProvider.php'] = <<<'BREVO_EOF'
<?php

namespace App\Providers;

use App\Services\Mail\BrevoApiTransport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

class BrevoMailServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Mail::extend('brevo-api', function (array $config = []) {
            return new BrevoApiTransport($config['key'] ?? config('services.brevo.key'));
        });
    }
}
BREVO_EOF;

function bak(string $path): void
{
    if (! file_exists($path . '.bak-brevo')) {
        copy($path, $path . '.bak-brevo');
    }
}

// ---------------------------------------------------------------------
// 1) Files
// ---------------------------------------------------------------------
echo "== Files ==\n";
foreach ($files as $rel => $content) {
    $path = $root . '/' . $rel;
    if (! is_dir(dirname($path))) {
        mkdir(dirname($path), 0755, true);
    }
    if (file_exists($path)) {
        if (file_get_contents($path) === $content) {
            echo "  same   $rel\n";
            continue;
        }
        bak($path);
        echo "  update $rel (backup: " . basename($path) . ".bak-brevo)\n";
    } else {
        echo "  create $rel\n";
    }
    file_put_contents($path, $content . "\n");
}

// ---------------------------------------------------------------------
// 2) bootstrap/providers.php
// ---------------------------------------------------------------------
echo "\n== Provider ==\n";
$provPath = $root . '/bootstrap/providers.php';

if (! file_exists($provPath)) {
    echo "  MANUAL bootstrap/providers.php paoa jayni. config/app.php er providers\n"
       . "         array te App\\Providers\\BrevoMailServiceProvider::class, add korun.\n";
} else {
    $prov = file_get_contents($provPath);
    if (str_contains($prov, 'BrevoMailServiceProvider')) {
        echo "  skip   already registered\n";
    } else {
        $new = preg_replace(
            '/(\]\s*;\s*)$/',
            "    App\\\\Providers\\\\BrevoMailServiceProvider::class,\n$1",
            rtrim($prov),
            1,
            $count
        );
        if ($count === 1) {
            bak($provPath);
            file_put_contents($provPath, $new . "\n");
            echo "  added  BrevoMailServiceProvider\n";
        } else {
            echo "  MANUAL bootstrap/providers.php er return array e ei line ta add korun:\n"
               . "         App\\Providers\\BrevoMailServiceProvider::class,\n";
        }
    }
}

// ---------------------------------------------------------------------
// 3) config/services.php
// ---------------------------------------------------------------------
echo "\n== config/services.php ==\n";
$svcPath = $root . '/config/services.php';

if (! file_exists($svcPath)) {
    echo "  MANUAL config/services.php paoa jayni\n";
} else {
    $svc = file_get_contents($svcPath);
    if (str_contains($svc, "'brevo'")) {
        echo "  skip   already ache\n";
    } else {
        $block = "\n    'brevo' => [\n        'key' => env('BREVO_API_KEY'),\n    ],\n";
        $new = preg_replace('/(\n\s*\]\s*;\s*)$/', $block . '$1', rtrim($svc), 1, $count);
        if ($count === 1) {
            bak($svcPath);
            file_put_contents($svcPath, $new . "\n");
            echo "  added  'brevo' => ['key' => env('BREVO_API_KEY')]\n";
        } else {
            echo "  MANUAL config/services.php er return array e ei block ta add korun:\n"
               . "         'brevo' => ['key' => env('BREVO_API_KEY')],\n";
        }
    }
}

// ---------------------------------------------------------------------
// 4) config/mail.php -> mailers.brevo-api
// ---------------------------------------------------------------------
echo "\n== config/mail.php ==\n";
$mailPath = $root . '/config/mail.php';

if (! file_exists($mailPath)) {
    echo "  MANUAL config/mail.php paoa jayni\n";
} else {
    $mailCfg = file_get_contents($mailPath);
    if (str_contains($mailCfg, "'brevo-api'")) {
        echo "  skip   already ache\n";
    } else {
        $block = "\n        'brevo-api' => [\n            'transport' => 'brevo-api',\n        ],\n";
        $new = preg_replace(
            "/('mailers'\s*=>\s*\[)/",
            '$1' . $block,
            $mailCfg,
            1,
            $count
        );
        if ($count === 1) {
            bak($mailPath);
            file_put_contents($mailPath, $new);
            echo "  added  mailers.brevo-api\n";
        } else {
            echo "  MANUAL config/mail.php er 'mailers' array e ei block ta add korun:\n"
               . "         'brevo-api' => ['transport' => 'brevo-api'],\n";
        }
    }
}

// ---------------------------------------------------------------------
// 5) Syntax check
// ---------------------------------------------------------------------
echo "\n== Syntax check ==\n";
$bad = 0;
$check = ['app/Services/Mail/BrevoApiTransport.php', 'app/Providers/BrevoMailServiceProvider.php',
          'bootstrap/providers.php', 'config/services.php', 'config/mail.php'];
foreach ($check as $rel) {
    $p = $root . '/' . $rel;
    if (! file_exists($p)) continue;
    $out = []; $code = 0;
    exec('php -l ' . escapeshellarg($p) . ' 2>&1', $out, $code);
    if ($code !== 0) {
        $bad++;
        echo "  ERROR  $rel\n    " . implode("\n    ", $out) . "\n";
    } else {
        echo "  ok     $rel\n";
    }
}

if ($bad > 0) {
    echo "\nSyntax error ache. *.bak-brevo theke restore kora jay.\n";
    exit(1);
}

echo <<<'DONE'

Install shesh. Ekhon .env e:

    MAIL_MAILER=brevo-api
    BREVO_API_KEY=your-brevo-api-key
    MAIL_FROM_ADDRESS=freelancerirfan402@gmail.com
    MAIL_FROM_NAME="EasyDoc"
    ADMIN_NOTIFY_EMAILS=freelancerirfan402@gmail.com

Tarpor:
    php artisan config:clear
    php artisan mail:test freelancerirfan402@gmail.com

DONE;
