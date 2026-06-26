<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendBulkDocuments extends Mailable
{
    use Queueable, SerializesModels;

    public $details;

    /**
     * Create a new message instance.
     *
     * @param array $details
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->subject($this->details['subject'])
                     ->view('email_templates.send_document');

        if (!empty($this->details['attachments']) && is_array($this->details['attachments'])) {
            foreach ($this->details['attachments'] as $attachmentPath) {
                if ($attachmentPath) {
                    $fileContents = file_get_contents($attachmentPath);
                    $fileName = basename(parse_url($attachmentPath, PHP_URL_PATH));
                    $mail->attachData($fileContents, $fileName);
                }
            }
        }

        return $mail;
    }
}
