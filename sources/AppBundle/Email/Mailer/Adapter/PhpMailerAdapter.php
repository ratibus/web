<?php

namespace AppBundle\Email\Mailer\Adapter;

use Afup\Site\Utils\Configuration;
use AppBundle\Email\Mailer\Message;
use PHPMailer;
use UnexpectedValueException;

class PhpMailerAdapter implements MailerAdapter
{
    /** @var string|null */
    private $smtpServer;
    /** @var string|null */
    private $tls;
    /** @var string|null */
    private $username;
    /** @var string|null */
    private $password;
    /** @var string|null */
    private $port;

    public function __construct($smtpServer, $tls, $username, $password, $port)
    {
        $this->smtpServer = $smtpServer;
        $this->tls = $tls;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
    }

    public static function createFromConfiguration(Configuration $configuration)
    {
        return new self(
            $configuration->obtenir('mails|serveur_smtp'),
            $configuration->obtenir('mails|tls'),
            $configuration->obtenir('mails|username'),
            $configuration->obtenir('mails|password'),
            $configuration->obtenir('mails|port')
        );
    }

    public function send(Message $message)
    {
        $from = $message->getFrom();
        if (null === $from) {
            throw new UnexpectedValueException('Trying to send a mail with no sender');
        }
        $phpMailer = $this->createPhpMailer();
        $phpMailer->setFrom($from->getEmail(), $from->getName());
        $phpMailer->isHTML($message->isHtml());
        $phpMailer->Subject = $message->getSubject();
        $phpMailer->Body = $message->getContent();
        foreach ($message->getRecipients() as $user) {
            $phpMailer->addAddress($user->getEmail(), $user->getName());
        }
        foreach ($message->getCc() as $user) {
            $phpMailer->addAddress($user->getEmail(), $user->getName());
        }
        foreach ($message->getBcc() as $user) {
            $phpMailer->addAddress($user->getEmail(), $user->getName());
        }
        foreach ($message->getAttachments() as $attachment) {
            $phpMailer->addAttachment($attachment->getPath(), $attachment->getName(), $attachment->getEncoding(), $attachment->getType());
        }

        $phpMailer->send();
    }

    /**
     * Génération et configuration de l'objet PHPMailer
     *
     * @return PHPMailer objet mailer configuré
     */
    private function createPhpMailer()
    {
        // Exceptions gérées
        $mailer = new PHPMailer(true);
        $mailer->CharSet = 'utf-8';
        if (null !== $this->smtpServer) {
            $mailer->IsSMTP();
            $mailer->Host = $this->smtpServer;
            $mailer->SMTPAuth = false;
        }
        if (null !== $this->tls && $this->tls !== '0') {
            $mailer->SMTPAuth = $this->tls;
            $mailer->SMTPSecure = 'tls';
        }
        if ($this->username) {
            $mailer->Username = $this->username;
        }
        if ($this->password) {
            $mailer->Password = $this->password;
        }
        if ($this->port) {
            $mailer->Port = $this->port;
        }

        return $mailer;
    }
}
