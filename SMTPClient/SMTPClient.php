<?php

namespace PHP_Library\SMTPClient;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class SMTP_Client
{
    protected PHPMailer $mailer;

    public function __construct(
        string $sender_name,
        string $username,
        string $password,
        string $host,
        int $port
    ) {
        $this->mailer = new PHPMailer();
        $this->mailer->Username = $username;
        $this->mailer->Password = $password;
        $this->mailer->Host = $host;
        $this->mailer->Port = $port;
        $this->mailer->setFrom($username, $sender_name);
        $this->mailer->isHTML(true);
        $this->mailer->isSMTP();
        $this->mailer->CharSet = "UTF-8";
        $this->mailer->Encoding = 'base64';
        $this->mailer->SMTPAuth   = true;
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    }

    public static function create_from_ini(string $ini_file): static
    {
        $smtp_config = parse_ini_file($ini_file, true);

        $required_fields = [
            'Sender',
            'Username',
            'Port',
            'Host',
            'Password'
        ];

        foreach ($required_fields as $name => $value) {
            if (!key_exists($name, $smtp_config)) {
                throw new \Error("Key '{$name}' is missing");
            }
        }

        $smtp_client = new self(
            sender_name: $smtp_config['Sender'],
            username: $smtp_config['Username'],
            password: $smtp_config['Password'],
            host: $smtp_config['Host'],
            port: $smtp_config['Port']
        );

        foreach ($smtp_config as $name => $value) {
            $smtp_client->$name = $value;
        }
        return $smtp_client;
    }

    public function mail_to(string $recipent, string $subject, string $html_body): static
    {
        try {
            $this->mailer->addAddress($recipent);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $html_body;
            $this->mailer->send();
        } catch (\Exception $e) {
            if ($this->mailer->SMTPDebug) {
                echo $e->getMessage();
            }
        }
        return $this;
    }

    public function __set($name, $value): void
    {
        if ('debug' === strtolower($name)) {
            if (!$value) {
                $this->mailer->SMTPDebug = SMTP::DEBUG_OFF;
            } else if (is_int($value)) {
                $this->mailer->SMTPDebug = $value;
            } else {
                $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
            }
        } else {
            $this->mailer->$name = $value;
        }
    }

    public function __get($name): mixed
    {
        return $this->mailer->$name;
    }
}
