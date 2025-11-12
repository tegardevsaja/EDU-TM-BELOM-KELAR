<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CertificateDelivery extends Mailable
{
    use Queueable, SerializesModels;

    public string $subjectLine;
    public string $bodyText;
    public string $attachmentPath;
    public string $attachmentName;
    public string $mime;

    public function __construct(string $subject, string $body, string $attachmentPath, string $attachmentName, string $mime)
    {
        $this->subjectLine = $subject;
        $this->bodyText = $body;
        $this->attachmentPath = $attachmentPath;
        $this->attachmentName = $attachmentName;
        $this->mime = $mime;
    }

    public function build()
    {
        $mail = $this->subject($this->subjectLine)
            ->view('emails.certificate_delivery')
            ->with([
                'bodyText' => $this->bodyText,
            ]);

        if (is_file($this->attachmentPath)) {
            $mail->attach($this->attachmentPath, [
                'as' => $this->attachmentName,
                'mime' => $this->mime,
            ]);
        }

        return $mail;
    }
}
