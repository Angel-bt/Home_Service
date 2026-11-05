<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class NoticeMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  list<string>  $lines
     */
    public function __construct(
        public string $title,
        public array $lines,
        public ?string $actionLabel = null,
        public ?string $actionUrl = null,
    ) {
    }

    public function build(): self
    {
        return $this->subject($this->title)->view('emails.notice');
    }
}
