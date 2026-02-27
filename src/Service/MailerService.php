<?php

namespace Diablo\Service;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

class MailerService
{
    private Mailer $mailer;

    public function __construct() {
        $dsn = $_ENV['MAILER_DSN'] ?? 'smtp://localhost'; 
        $transport = Transport::fromDsn($dsn);
        $this->mailer = new Mailer($transport);
    }

    public function sendReportWithPdf(string $adminEmail, string $buildName, string $pdfContent): void {
        $email = (new Email())
            ->from('system@diablo-builds.fr')
            ->to($adminEmail)
            ->subject('⚠️ Signalement de Build : ' . $buildName)
            ->html("<p>L'administrateur a reçu un signalement pour le build : <strong>{$buildName}</strong>.</p><p>Le détail est en pièce jointe.</p>")
            ->attach($pdfContent, 'signalement-' . date('Ymd') . '.pdf', 'application/pdf');

        $this->mailer->send($email);
    }
}