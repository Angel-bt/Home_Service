<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\NoticeMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

final class SendNoticeEmail implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    /**
     * @param  list<string>  $lines
     */
    public function __construct(
        public string $to,
        public string $title,
        public array $lines,
        public ?string $actionLabel = null,
        public ?string $actionUrl = null,
    ) {
        $this->onQueue('mail');
    }

    public function handle(): void
    {
        Mail::to($this->to)->send(new NoticeMail($this->title, $this->lines, $this->actionLabel, $this->actionUrl));
    }
}
