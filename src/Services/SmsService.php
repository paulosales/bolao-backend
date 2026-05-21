<?php
declare(strict_types=1);

namespace App\Services;

use Psr\Log\LoggerInterface;

class SmsService
{
    public function __construct(
        private string $sid,
        private string $token,
        private string $from,
        private LoggerInterface $logger
    ) {
    }

    public function sendInvite(string $toPhone, string $poolName, string $inviteLink): bool
    {
        $message = "Você foi convidado para o bolão '{$poolName}' da Copa 2026! Acesse: {$inviteLink}";
        return $this->send($toPhone, $message);
    }

    public function send(string $toPhone, string $message): bool
    {
        if (empty($this->sid) || empty($this->token)) {
            $this->logger->warning('Twilio não configurado. Pulando SMS.', ['to' => $toPhone]);
            return false;
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->sid}/Messages.json";
        $data = [
            'From' => $this->from,
            'To'   => $toPhone,
            'Body' => $message,
        ];

        $context = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => [
                    'Authorization: Basic ' . base64_encode("{$this->sid}:{$this->token}"),
                    'Content-Type: application/x-www-form-urlencoded',
                ],
                'content' => http_build_query($data),
                'ignore_errors' => true,
            ],
        ]);

        $response = file_get_contents($url, false, $context);
        if ($response === false) {
            $this->logger->error('Falha ao enviar SMS via Twilio.', ['to' => $toPhone]);
            return false;
        }

        $json = json_decode($response, true);
        if (isset($json['error_code'])) {
            $this->logger->error('Twilio retornou erro: ' . ($json['message'] ?? ''), ['to' => $toPhone]);
            return false;
        }

        return true;
    }
}
