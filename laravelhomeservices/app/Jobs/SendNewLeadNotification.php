<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\NewLeadNotificationMail;
use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

final class SendNewLeadNotification implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(public Contact $contact)
    {
        $this->onQueue('mail');
    }

    public function handle(): void
    {
        $recipient = (string) config('marketplace.lead_notification_address');

        if ($recipient === '') {
            return;
        }

        Mail::to($recipient)->send(new NewLeadNotificationMail($this->contact));
    }
}
