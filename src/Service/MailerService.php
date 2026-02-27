<?php

namespace Diablo\Service;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

class MailerService
{
    private Mailer $mailer;

    public function __construct()
    {
        // Remplace par ton DSN MailTrap ou SMTP perso
        // Format : smtp://USERNAME:PASSWORD@smtp.mailtrap.io:2525
        $dsn = 'smtp://ton_username:ton_password@sandbox.smtp.mailtrap.io:2525';
        $transport = Transport::fromDsn($dsn);
        $this->mailer = new Mailer($transport);
    }

    public function sendReportWithPdf(string $adminEmail, string $buildName, string $pdfContent): void
    {
        $email = (new Email())
            ->from('system@diablo-builds.fr')
            ->to($adminEmail)
            ->subject('⚠️ Signalement de Build : ' . $buildName)
            ->text('Un utilisateur a signalé le build suivant. La fiche détaillée est jointe en PDF.')
            ->attach($pdfContent, 'signalement-' . date('Ymd') . '.pdf', 'application/pdf');

        $this->mailer->send($email);
    }
}