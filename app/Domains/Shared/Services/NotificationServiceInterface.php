<?php

namespace App\Domains\Shared\Services;

interface NotificationServiceInterface
{
    /**
     * Envia um e-mail com opcional de anexo PDF.
     *
     * @param string|array $to
     * @param string $subject
     * @param array $emailData (body, signature, hasAttachment, etc)
     * @param string|null $attachmentContent
     * @param string|null $attachmentName
     * @param string|null $cc
     * @return bool
     */
    public function sendEmailWithPdf($to, string $subject, array $emailData, $attachmentContent = null, ?string $attachmentName = null, ?string $cc = null): bool;

    /**
     * Gera um PDF a partir de uma view e dados.
     *
     * @param string $view
     * @param array $data
     * @param string $paper (A4, etc)
     * @param string $orientation (portrait, landscape)
     * @return string (conteúdo binário do PDF)
     */
    public function generatePdf(string $view, array $data, string $paper = 'A4', string $orientation = 'portrait'): string;

    /**
     * Registra um log de e-mail no banco de dados.
     *
     * @param array $logData
     * @return void
     */
    public function logEmail(array $logData): void;
}
