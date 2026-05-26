<?php
declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Psr\Log\LoggerInterface;

class EmailService
{
    public function __construct(
        private string $host,
        private int $port,
        private string $user,
        private string $pass,
        private string $from,
        private string $fromName,
        private LoggerInterface $logger
    ) {
    }

    public function sendInvite(string $toEmail, string $toName, string $poolName, string $inviteLink): bool
    {
        $subject = "Você foi convidado para o Bolão: {$poolName}";
        $body = $this->buildInviteEmailHtml($toName, $poolName, $inviteLink);
        return $this->send($toEmail, $toName, $subject, $body);
    }

    public function sendPasswordReset(string $toEmail, string $toName, string $resetLink): bool
    {
        $subject = 'Redefinição de senha – Bolão da Copa 2026';
        $body    = $this->buildPasswordResetEmailHtml($toName, $resetLink);
        return $this->send($toEmail, $toName, $subject, $body);
    }

    public function send(string $toEmail, string $toName, string $subject, string $body): bool
    {
        if (empty($this->host)) {
            $this->logger->warning('E-mail não configurado. Pulando envio.', ['to' => $toEmail]);
            return false;
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $this->host;
            $mail->SMTPAuth = true;
            $mail->Username = $this->user;
            $mail->Password = $this->pass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->port;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($this->from, $this->fromName);
            $mail->addAddress($toEmail, $toName);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = strip_tags(str_replace('<br>', "\n", $body));

            $mail->send();
            return true;
        } catch (\Throwable $e) {
            $this->logger->error('Falha ao enviar e-mail: ' . $e->getMessage(), ['to' => $toEmail]);
            return false;
        }
    }

    private function buildPasswordResetEmailHtml(string $name, string $link): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <body style="font-family:Arial,sans-serif;background:#f5f5f5;padding:20px">
            <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;padding:30px">
                <h1 style="color:#1a56db">⚽ Bolão da Copa 2026</h1>
                <p>Olá <strong>{$name}</strong>,</p>
                <p>Recebemos uma solicitação para redefinir a senha da sua conta.</p>
                <p>Clique no botão abaixo para criar uma nova senha. O link é válido por <strong>1 hora</strong>.</p>
                <div style="text-align:center;margin:30px 0">
                    <a href="{$link}" style="background:#1a56db;color:#fff;padding:14px 28px;border-radius:6px;text-decoration:none;font-size:16px">
                        Redefinir Senha
                    </a>
                </div>
                <p style="color:#777;font-size:12px">Ou copie e cole este link: {$link}</p>
                <p style="color:#777;font-size:12px">Se você não solicitou a redefinição de senha, ignore este e-mail.</p>
            </div>
        </body>
        </html>
        HTML;
    }

    private function buildInviteEmailHtml(string $name, string $poolName, string $link): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <body style="font-family:Arial,sans-serif;background:#f5f5f5;padding:20px">
            <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;padding:30px">
                <h1 style="color:#1a56db">⚽ Bolão da Copa 2026</h1>
                <p>Olá <strong>{$name}</strong>,</p>
                <p>Você foi convidado para participar do bolão <strong>{$poolName}</strong>!</p>
                <p>Clique no botão abaixo para aceitar o convite e começar a apostar:</p>
                <div style="text-align:center;margin:30px 0">
                    <a href="{$link}" style="background:#1a56db;color:#fff;padding:14px 28px;border-radius:6px;text-decoration:none;font-size:16px">
                        Aceitar Convite
                    </a>
                </div>
                <p style="color:#777;font-size:12px">Ou copie e cole este link: {$link}</p>
                <p style="color:#777;font-size:12px">Este convite expira em 7 dias.</p>
            </div>
        </body>
        </html>
        HTML;
    }
}
