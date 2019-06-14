<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\TicketMessage;
use App\Entity\User;
use App\Services\NotificationService;
use App\Services\TicketService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/ticketPanel")
 */
class TicketController extends AbstractController
{
    private $ticketService;
    private $notificationService;

    public function __construct( TicketService $ticketService , NotificationService $notificationService )
    {
        $this->ticketService = $ticketService;
        $this->notificationService = $notificationService;
    }

    /**
     * @Route("/", name="dashboard")
     */
    public function dashboardAction( Request $request ): Response
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder( $user )
            ->add('smsNotifyPermission', CheckboxType::class, [
                'required' => false
            ])
            ->add('pushNotifyPermission', CheckboxType::class, [
                'required' => false
            ])
            ->add('submit' , SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ){
            $em->persist($user);
            $em->flush();
        }

        $repo = $this->getDoctrine()->getRepository(Ticket::class);
        if( $user->getRole() == 'ROLE_ADMIN' ){
            $list = $repo->findFromAdmin();
        }else{
            $list = $repo->findFromUser($user);
        }

        return $this->render('dashboard.html.twig' , [
            'list'              => $list,
            'form_notify'       => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit-ticket")
     */
    public function editTicketAction( int $id = 0 , Request $request): Response
    {
        $user = $this->getUser();

        if(! $user ){
            throw new \Exception('Utente non trovato', 100);
        }
        $em = $this->getDoctrine()->getManager();

        $permission = $this->ticketService->PermissionFromTicketId( $id , $user);

        if(! $permission ){
            $this->ticketService->PermissionException();
        }

        if( $id>0 ){
            $Ticket = $em->getRepository(Ticket::class)->find($id);
        }

        //Creo form per nuovo ticket
        $TicketMessage = new TicketMessage();
        $form = $this->createFormBuilder( $TicketMessage )
            ->add('message', TextareaType::class )
            ->add('submit', SubmitType::class)
            ->getForm()
        ;

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ){
            $is_new = false;
            if( $id == 0 ){//Nuovo ticket
                $Ticket = new Ticket();
                if( $user->getRole() == 'ROLE_ADMIN' ){
                    throw new \Exception('Utente admin non può aprire nuovi ticket', 110);
                }else{
                    $Ticket->setOpenFromUser( $user );
                }
                $Ticket->setStatus(1);//Ticket aperto

                $is_new = true;
            }

            $Ticket->setDateLastUpdatedAt( new \DateTime() );
            $TicketMessage->setAuthorOwner( $user );
            $TicketMessage->setTicket( $Ticket );

            $em->persist( $Ticket );
            $em->persist( $TicketMessage );
            $em->flush();

            if( $is_new ){
                $this->notificationService->setNotification( $this->notificationService::CREATE , $user , $Ticket );
            }else if( $user->getRole() == 'ROLE_ADMIN' ){
                $this->ticketService->assignTicket( $Ticket , $user );//Assegna il ticket

                $this->notificationService->setNotification( $this->notificationService::NEW_MESSAGE_FROM_ADMIN , $user , $Ticket , $TicketMessage);
            }else if( $user->getRole() == 'ROLE_USER' ){
                $this->notificationService->setNotification( $this->notificationService::NEW_MESSAGE_FROM_USER , $user , $Ticket , $TicketMessage );
            }

            return $this->redirectToRoute('edit-ticket' , ['id' => $Ticket->getId()]);
        }

        return $this->render('edit_ticket.html.twig', [
            'ticket'            => $Ticket ?? null,
            'user'              => $user,
            'listMessages'      => $this->listTicketMessages( $id ),
            'form'              => $form->createView()
        ]);
    }

    /**
     * @param $ticket_id
     * @return array
     * Ritorna elenco dei messaggi per uno specifico ticket, oppure un elenco vuoto
     */
    private function listTicketMessages( $ticket_id ): array
    {
        $em = $this->getDoctrine()->getManager();
        if( $ticket_id>0 ){
            $Ticket = $em->getRepository(Ticket::class)->find($ticket_id);
            $repo = $em->getRepository(TicketMessage::class);
            $listMessages = $repo->findList( $Ticket );
        }else{
            $listMessages = [];
        }

        return $listMessages;
    }

    /**
     * @Route("/close/{id}", name="close-ticket")
     */
    public function closeTicketAction( int $id ): Response
    {
        $user = $this->getUser();

        $this->ticketService->closeTicketFromId( $id , $user );

        return $this->redirectToRoute('dashboard', ['ticketClosed' => $id]);
    }

    /**
     * @Route("/assign_ticket/{id}", name="assign-ticket")
     */
    public function assignTicketAction( int $id ): Response
    {
        $user = $this->getUser();

        $this->ticketService->assignTicketFromId($id,$user);

        return $this->redirectToRoute('dashboard' , ['ticketAssigned' => 1]);
    }

    /**
     * @Route("/assign_ticket_other_admin/{id}", name="assign-ticket-other-admin")
     */
    public function assignTicketOtherAdminAction( int $id , Request $request ): Response
    {
        $user = $this->getUser();
        $admin_list = $this->getDoctrine()->getManager()->getRepository(User::class)->findAdmin();

        if(! is_null( $request->query->get('to_user') ) ){
            return $this->setAssignTicketOtherAdminAction( $id , $request->query->get('to_user') );
        }

        return $this->render('assign_ticket_other_admin.html.twig', [
            'ticket_id'         => $id,
            'user'              => $user,
            'admin_list'        => $admin_list,
        ]);
    }


    public function setAssignTicketOtherAdminAction( int $id , int $to_user_id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $Ticket = $em->getRepository(Ticket::class)->find($id);
        $toUser = $em->getRepository(User::class)->find($to_user_id);
        
        $this->ticketService->setTicketOwner($Ticket,$Ticket->getOwnerAdmin(),$toUser);
        
        return $this->redirectToRoute('dashboard' , [
            'ticketAssigned' => 1
        ]);
    }
}