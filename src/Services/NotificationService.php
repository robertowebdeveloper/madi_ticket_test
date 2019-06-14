<?php

namespace App\Services;


use App\Entity\Notify;
use App\Entity\Ticket;
use App\Entity\TicketMessage;
use App\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NotificationService
{
    private $container;
    private $em;

    const
        CREATE = 1,
        NEW_MESSAGE_FROM_USER = 2,
        NEW_MESSAGE_FROM_ADMIN = 3,
        CLOSE_NO_OWNER = 4,
        CLOSE_FROM_USER = 5,
        CLOSE_FROM_ADMIN = 6;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getManager();
    }

    public function setNotification( int $type , User $user , Ticket $ticket = null , TicketMessage $ticketMessage = null ): bool
    {
        $status = true;
        $repo = $this->em->getRepository(User::class);


        if( $type == self::CREATE ) {
            $message = "L'utente {$user->getName()} ha inserito il nuovo ticket #{$ticket->getId()}";

            $admin_list = $repo->findAdmin();
            foreach ($admin_list as $admin) {//Avviso tutti gli admin
                $status *= $this->sendNotification($type , $message, $admin , $ticket);
            }
        }else if( $type == self::NEW_MESSAGE_FROM_USER && ! is_null($ticket->getOwnerAdmin()) ){
            $message = "L'utente {$user->getName()} ha scritto un nuovo messaggio sul ticket #{$ticket->getId()}";

            $status = $this->sendNotification($type ,  $message , $ticket->getOwnerAdmin() , $ticket , $ticketMessage);
        }else if( $type == self::NEW_MESSAGE_FROM_ADMIN && ! is_null($ticket->getOpenFromUser()) ){
            $message = "L'amministatore {$user->getName()} ha risposto al ticket #{$ticket->getId()}";

            $status = $this->sendNotification($type ,  $message , $ticket->getOpenFromUser() , $ticket, $ticketMessage );
        }else if( $type == self::CLOSE_NO_OWNER ){
            $message = "L'utente {$user->getName()} ha chiuso il ticket #{$ticket->getId()} ma nessuno lo aveva preso in carico";

            $admin_list = $repo->findAdmin();
            foreach ($admin_list as $admin) {//Avviso tutti gli admin
                $status *= $this->sendNotification($type , $message, $admin, $ticket);
            }
        }else if( $type == self::CLOSE_FROM_USER && ! is_null($ticket->getOwnerAdmin() ) ){
            $message = "L'utente {$user->getName()} ha chiuso il ticket #{$ticket->getId()}";

            $this->sendNotification($type , $message , $ticket->getOwnerAdmin(), $ticket);
        }else if( $type == self::CLOSE_FROM_ADMIN && ! is_null($ticket->getOpenFromUser()) ){
            $message = "L'amministratore {$user->getName()} ha chiuso il ticket #{$ticket->getId()}";

            $this->sendNotification($type , $message , $ticket->getOpenFromUser(), $ticket);
        }

        return $status;
    }

    private function sendNotification( int $type , string $message , User $user , Ticket $ticket = null , TicketMessage $ticketMessage = null): bool
    {
        $status = true;

        //Crea una nuova notifica
        $notify = new Notify();
        $notify->setType($type);
        $notify->setTicket($ticket);
        $notify->setTicketMessage($ticketMessage);

        if( $user->getEmailNotifyPermission() || true ){//al momento la mail viene inviata SEMPRE, quindi a prescindere dal permesso (che comunque dovrebbe essere sempre VERO), viene inviata
            $s = $this->sendEmail( $message , $user->getEmail() );
            if( $s ){
                $notify->setEmailSendedAt( new \DateTime() );
            }
            $status *= $s;
        }
        if( $user->getSmsNotifyPermission() ) {
            $s = $this->sendSms($message, $user->getMobile());
            if( $s ){
                $notify->setSmsSendedAt( new \DateTime() );
            }
            $status *= $s;
        }
        if( $user->getPushNotifyPermission() ){
            $s = $this->sendPush( $message , $user->getDeviceRegistrationId() );
            if( $s ){
                $notify->setPushSendedAt( new \DateTime() );
            }
            $status *= $s;
        }

        //Salva notifica a DB
        $this->em->persist($notify);
        $this->em->flush();

        return $status;
    }

    private function sendEmail( string $message , string $email ): bool
    {
        //Invio email (es. invio con sendmail locale, servizio esterno come mailchimp o Amazon SES)
        return true;
    }

    private function sendSms( string $message , string $mobile ): bool
    {
        //Invio sms (es. SendInBlue, Mobyt ecc...)
        return true;
    }

    private function sendPush( string $message , string $deviceRegistrationId = null ): bool
    {
        //Invio push notification (es. Amazon SNS, OneSignal, ecc...)
        return true;
    }
}