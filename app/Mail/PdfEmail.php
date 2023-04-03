<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PdfEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $pdf;
    protected $fileName;
    protected $data;
    public $subject;

    public function __construct($pdf, $fileName, $data, $subject)
    {
        $this->pdf = $pdf;
        $this->fileName = $fileName;
        $this->data = $data;
        $this->subject = $subject;
    }

    public function build()
    {
        $mail = $this->view('email')
            ->with($this->data)
            ->subject($this->subject);
        if ($this->pdf != null) {
            $mail->attachData($this->pdf, $this->fileName);
        }
        return $mail;
    }
}
