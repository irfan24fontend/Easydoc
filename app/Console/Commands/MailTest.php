<?php

namespace App\Console\Commands;

use App\Mail\NoticeMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class MailTest extends Command
{
    protected $signature = 'mail:test {to : Je email e test mail jabe}';

    protected $description = 'SMTP setting thik ache kina test email pathay';

    public function handle(): int
    {
        $this->line('Mailer : ' . config('mail.default'));
        $this->line('Host   : ' . config('mail.mailers.' . config('mail.default') . '.host'));
        $this->line('From   : ' . config('mail.from.address'));
        $this->newLine();

        try {
            Mail::to($this->argument('to'))->send(new NoticeMail('EasyDoc test email', [
                'tone'    => 'success',
                'badge'   => 'Test',
                'title'   => 'Email setup kaj korche',
                'greeting' => 'Hello,',
                'lines'   => ['Eta ekta test email. Eta dekhte pele EasyDoc er email setting thik ache.'],
                'details' => ['Time' => now()->format('d M Y, h:i A'), 'Mailer' => config('mail.default')],
                'button'  => ['text' => 'EasyDoc kholo', 'url' => config('app.url')],
                'appUrl'  => config('app.url'),
            ]));
        } catch (\Throwable $e) {
            $this->error('Failed: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info('Test email pathano hoyeche: ' . $this->argument('to') . ' (inbox ar spam dekhun)');

        return self::SUCCESS;
    }
}
