<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
use Throwable;

/**
 * Connects and logs in to the mail server without sending anything. It runs with the daily cron,
 * so a revoked password shows up in the logs before someone fails to recover their account.
 */
final class CheckMail extends Command
{
    protected $signature = 'mail:check';

    protected $description = 'Log in to the SMTP server without sending any message';

    public function handle(): int
    {
        $transport = Mail::mailer()->getSymfonyTransport();

        if (! $transport instanceof SmtpTransport) {
            Log::info('mail:check sin SMTP (' . config('mail.default') . ')');
            $this->info('sin SMTP');

            return self::SUCCESS;
        }

        try {
            $transport->start();
            $transport->stop();
        } catch (Throwable $exception) {
            Log::error('mail:check: ' . $exception->getMessage());
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        Log::info('mail:check smtp: ok');
        $this->info('smtp: ok');

        return self::SUCCESS;
    }
}
