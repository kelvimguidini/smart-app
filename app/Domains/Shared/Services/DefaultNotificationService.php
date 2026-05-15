<?php

namespace App\Domains\Shared\Services;

use App\Mail\PdfEmail;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DefaultNotificationService implements NotificationServiceInterface
{
    /**
     * @inheritDoc
     */
    public function sendEmailWithPdf($to, string $subject, array $emailData, $attachmentContent = null, ?string $attachmentName = null, ?string $cc = null): bool
    {
        try {
            $toAddresses = is_array($to) ? $to : explode(";", $to);
            $mail = Mail::to($toAddresses);

            if ($cc) {
                $mail->cc($cc);
            }

            $mail->send(new PdfEmail($attachmentContent, $attachmentName ?? 'documento.pdf', $emailData, $subject));
            return true;
        } catch (\Exception $e) {
            Log::error('Falha no envio de e-mail: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function generatePdf(string $view, array $data, string $paper = 'A4', string $orientation = 'portrait'): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', base_path('public'));

        $dompdf = new Dompdf($options);
        $html = view($view, $data)->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper($paper, $orientation);
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * @inheritDoc
     */
    public function logEmail(array $logData): void
    {
        DB::table('email_log')->insert(array_merge([
            'created_at' => now(),
        ], $logData));
    }
}
