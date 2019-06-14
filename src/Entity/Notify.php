<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * @ORM\Entity()
 * @ORM\Table(name="notify", indexes={@Index(columns={"type"}), @Index(columns={"deleted_at"}), @Index(columns={"created_at"}) })
 */
class Notify
{
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * 1: ticket creato
     * 2: ticket ha nuovo messaggio
     * 3: se amministratore risponde a ticket
     * 4: se il ticket viene chiuso ma non è stato preso in carico da nessun amministratore
     * 5: se il ticket viene chiuso dall'utente ed un amministratore ha preso in carico il ticket
     * 6: se il ticket viene chiuso dall'amministratore
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Ticket")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * valorizzato se la notifica è collegato ad un ticket (esempio per l'apertura del ticket)
     */
    private $Ticket = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TicketMessage")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * valorizzato se la notifica è collegato ad un messaggio
     */
    private $TicketMessage = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * data/ora di invio della notifica per e-mail
     */
    private $emailSendedAt = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * data/ora di invio della notifica per sms
     */
    private $smsSendedAt = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * data/ora di invio della notifica per push notification
     */
    private $pushSendedAt = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $message = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt = null;

    /**
     * @return mixed
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getTicket(): App\Entity\Ticket
    {
        return $this->Ticket;
    }

    /**
     * @param mixed $Ticket
     */
    public function setTicket($Ticket): void
    {
        $this->Ticket = $Ticket;
    }

    /**
     * @return mixed
     */
    public function getTicketMessage(): App\Entity\TicketMessage
    {
        return $this->TicketMessage;
    }

    /**
     * @param mixed $TicketMessage
     */
    public function setTicketMessage($TicketMessage): void
    {
        $this->TicketMessage = $TicketMessage;
    }

    /**
     * @return mixed
     */
    public function getEmailSendedAt(): \DateTime
    {
        return $this->emailSendedAt;
    }

    /**
     * @param mixed $emailSendedAt
     */
    public function setEmailSendedAt($emailSendedAt): void
    {
        $this->emailSendedAt = $emailSendedAt;
    }

    /**
     * @return mixed
     */
    public function getSmsSendedAt(): \DateTime
    {
        return $this->smsSendedAt;
    }

    /**
     * @param mixed $smsSendedAt
     */
    public function setSmsSendedAt($smsSendedAt): void
    {
        $this->smsSendedAt = $smsSendedAt;
    }

    /**
     * @return mixed
     */
    public function getPushSendedAt(): \DateTime
    {
        return $this->pushSendedAt;
    }

    /**
     * @param mixed $pushSendedAt
     */
    public function setPushSendedAt($pushSendedAt): void
    {
        $this->pushSendedAt = $pushSendedAt;
    }

    /**
     * @return mixed
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message): void
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getDeletedAt(): \DateTime
    {
        return $this->deletedAt;
    }

    /**
     * @param mixed $deletedAt
     */
    public function setDeletedAt($deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }
}