<?php

namespace App\Services;


use App\Entity\Ticket;
use App\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TicketService
{
    private $container;
    private $em;
    private $notificationService;

    public function __construct(ContainerInterface $container , NotificationService $notificationService)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getManager();
        $this->notificationService = $notificationService;
    }

    public function Permission(Ticket $Ticket, User $User): bool
    {
        return (
                $User->getRole() == 'ROLE_ADMIN'
                &&
                (
                    is_null($Ticket->getOwnerAdmin())
                    ||
                    $Ticket->getOwnerAdmin() == $User
                )
            )
            ||
            (
                $User->getRole() == 'ROLE_USER'
                &&
                $Ticket->getOpenFromUser() == $User
            );
    }

    public function PermissionFromTicketId(int $id, User $User): bool
    {
        if ($id > 0) {
            $Ticket = $this->em->getRepository(Ticket::class)->find($id);
            if ($Ticket) {
                return $this->Permission($Ticket, $User);
            }
        } else if ($User->getRole() == 'ROLE_USER') {
            return true;
        }
        return false;
    }

    public function PermissionException()
    {
        throw new \Exception('Non hai i permessi per gestire questo ticket', 120);
    }

    public function assignTicketFromId(int $id, User $user): void
    {
        $Ticket = $this->em->getRepository(Ticket::class)->find($id);
        $this->assignTicket($Ticket, $user);
    }

    public function assignTicket(Ticket $Ticket, User $user): void
    {
        $p = $this->Permission($Ticket , $user);
        if(! $p || $user->getRole() != 'ROLE_ADMIN' ){
            $this->PermissionException();
        }

        $Ticket->setStatus(2);
        $Ticket->setOwnerAdmin($user);
        $Ticket->setDateLastUpdatedAt( new \DateTime() );
        $this->em->persist($Ticket);
        $this->em->flush();
    }

    public function closeTicketFromId(int $id, User $user): void
    {
        $Ticket = $this->em->getRepository(Ticket::class)->find($id);
        $this->closeTicket($Ticket, $user);
    }

    public function closeTicket(Ticket $Ticket, User $user): void
    {
        $p = $this->Permission($Ticket , $user);
        if(! $p ){
            $this->PermissionException();
        }

        $Ticket->setStatus(3);
        $Ticket->setDateLastUpdatedAt( new \DateTime() );
        $this->em->persist($Ticket);
        $this->em->flush();

        if( is_null( $Ticket->getOwnerAdmin() ) ){
            $this->notificationService->setNotification( $this->notificationService::CLOSE_NO_OWNER , $user , $Ticket);
        }else if( $user->getRole() == 'ROLE_ADMIN' ){
            $this->notificationService->setNotification( $this->notificationService::CLOSE_FROM_ADMIN , $user , $Ticket);
        }else{
            $this->notificationService->setNotification( $this->notificationService::CLOSE_FROM_USER , $user , $Ticket);
        }
    }

    public function setTicketOwner( Ticket $Ticket , User $fromUser , User $toUser ): bool
    {
        if( $fromUser->getRole() != 'ROLE_ADMIN' ){
            throw new \Exception("L'utente non ha i permessi", 200);
        }
        if(! is_null($toUser) && $toUser->getRole() != 'ROLE_ADMIN' ){
            throw new \Exception("L'utente non ha i permessi", 201);
        }

        if( $Ticket->getOwnerAdmin() == $fromUser ){//Trasferisce il ticket da un admin ad un altro
            $Ticket->setOwnerAdmin( $toUser );
            $this->em->persist( $Ticket );
            $this->em->flush();
            return true;
        }else{
            return false;
        }
    }
}