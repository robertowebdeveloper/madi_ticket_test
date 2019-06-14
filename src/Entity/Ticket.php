<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TicketRepository")
 * @ORM\Table(name="ticket", indexes={@Index(columns={"deleted_at"}), @Index(columns={"status"}) })
 */
class Ticket
{
    public function __construct()
    {
        $this->dateOpenedAt = new \DateTime();
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateOpenedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateLastUpdatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * Utente che ha aperto il ticket
     */
    private $openFromUser;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * Amministratore che sta gestendo il ticket
     */
    private $ownerAdmin;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * 1: nuovo ticket
     * 2: ticket preso in carico
     * 3: ticket chiuso
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * cancellazione logica
     */
    private $deletedAt;

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
    public function getDateOpenedAt(): \DateTime
    {
        return $this->dateOpenedAt;
    }

    /**
     * @param mixed $dateOpenedAt
     */
    public function setDateOpenedAt($dateOpenedAt): void
    {
        $this->dateOpenedAt = $dateOpenedAt;
    }

    /**
     * @return mixed
     */
    public function getDateLastUpdatedAt(): \DateTime
    {
        return $this->dateLastUpdatedAt;
    }

    /**
     * @param mixed $dateLastUpdatedAt
     */
    public function setDateLastUpdatedAt($dateLastUpdatedAt): void
    {
        $this->dateLastUpdatedAt = $dateLastUpdatedAt;
    }

    /**
     * @return mixed
     */
    public function getOpenFromUser(): ?User
    {
        return $this->openFromUser;
    }

    /**
     * @param mixed $openFromUser
     */
    public function setOpenFromUser($openFromUser): void
    {
        $this->openFromUser = $openFromUser;
    }

    /**
     * @return mixed
     */
    public function getOwnerAdmin(): ?User
    {
        return $this->ownerAdmin;
    }

    /**
     * @param mixed $ownerAdmin
     */
    public function setOwnerAdmin($ownerAdmin): void
    {
        $this->ownerAdmin = $ownerAdmin;
    }

    /**
     * @return mixed
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    public function getStatusDescription(): string
    {
        switch( $this->status ){
            default:
            case 1:
                $l = 'Nuovo';
                break;
            case 2:
                $l = 'Preso in carico';
                break;
            case 3:
                $l = 'Chiuso';
                break;
        }
        return $l;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
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
