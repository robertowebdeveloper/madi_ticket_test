<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\TicketMessage;
use App\Entity\User;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\NotificationService;
use App\Services\TicketService;
use App\Form\TicketMessageType;


/**
 * @Route("/api")
 */
class TicketRestController extends AbstractFOSRestController
{
    private $ticketService;
    private $notificationService;

    private $user;//Da sostituire con sistema di sicurezza per autenticazione

    public function __construct( TicketService $ticketService , NotificationService $notificationService )
    {
        $this->ticketService = $ticketService;
        $this->notificationService = $notificationService;
    }

    /**
     * @Rest\Get("/{user_id}/list")
     * @return Response
     */
    public function getListAction( int $user_id ): Response
    {
        $user = $this->getUserFromId( $user_id );
        $repo = $this->getDoctrine()->getManager()->getRepository(Ticket::class);
        $list = $repo->findFromUser( $user );

        return $this->handleView( $this->view($list) );
    }

    /**
     * @Rest\Get("/{user_id}/ticket/{id}")
     * @return Response
     */
    public function getTicketAction( int $user_id , int $id ): Response
    {
        $user = $this->getUserFromId( $user_id );
        $repo = $this->getDoctrine()->getManager()->getRepository(Ticket::class);
        $ticket = $repo->findBy([
            'openFromUser'  => $user,
            'id'            => $id,
            'deletedAt'     => null
        ]);

        return $this->handleView( $this->view($ticket) );
    }

    /**
     * @Rest\Post("/new")
     * @return Response
     *
     * come dati in POST passare:
     * user_id
     * message (stringa del messaggio)
     */
    public function newTicketAction(Request $request): Response
    {
        if(is_null( $request->request->get('user_id') )){
            throw new \Exception('In questa versione è importante passare l\'user_id, al posto del token, per operare');
        }

        $user = $this->getUserFromId( $request->request->get('user_id') );
        if(! $user ){
            throw new \Exception('Utente non trovato', 100);
        }

        $em = $this->getDoctrine()->getManager();

        $Ticket = new Ticket();
        $Ticket->setStatus(1);
        $Ticket->setOpenFromUser($user);
        $Ticket->setDateOpenedAt( new \DateTime() );
        $Ticket->setDateLastUpdatedAt( new \DateTime() );

        $TicketMessage = new TicketMessage();
        $form = $this->createForm(TicketMessageType::class, $TicketMessage);

        $data = [
          'message' => $request->request->get('message')
        ];
        $form->submit( $data );

        if ($form->isSubmitted() && $form->isValid()) {
            $TicketMessage->setTicket($Ticket);
            $em->persist( $Ticket );
            $em->persist( $TicketMessage );
            $em->flush();

            $this->notificationService->setNotification( $this->notificationService::CREATE , $user , $Ticket );

            return $this->handleView($this->view(['status' => 'ok' , 'id' => $Ticket->getId()], Response::HTTP_CREATED));
        }

        return $this->handleView($this->view($form->getErrors()));
    }

    /**
     * @Rest\Post("/new_message")
     * @return Response
     *
     * come dati in POST passare:
     * user_id
     * ticket_id
     * message (stringa del messaggio)
     */
    public function newTicketMessageAction(Request $request): Response
    {
        if(is_null( $request->request->get('user_id') )){
            throw new \Exception('In questa versione è importante passare l\'user_id, al posto del token, per operare');
        }

        $user = $this->getUserFromId( $request->request->get('user_id') );
        if(! $user ){
            throw new \Exception('Utente non trovato', 100);
        }

        $em = $this->getDoctrine()->getManager();

        $TicketMessage = new TicketMessage();
        $form = $this->createForm(TicketMessageType::class, $TicketMessage);

        if(! $request->request->get('ticket_id')  ){
            throw new \Exception('Ticket non specificato', 300);
        }

        $repo = $this->getDoctrine()->getRepository(Ticket::class);
        $Ticket = $repo->find( $request->request->get('ticket_id') );

        if( is_null($Ticket) ){
            throw new \Exception('Ticket non specificato', 300);
        }else{
            $data = [
                'message' => $request->request->get('message')
            ];
            $form->submit( $data );

            if ($form->isSubmitted() && $form->isValid()) {
                $Ticket->setDateLastUpdatedAt( new \DateTime() );
                $TicketMessage->setTicket($Ticket);
                $em->persist( $Ticket );
                $em->persist( $TicketMessage );
                $em->flush();

                $type = ( $user->getRole() == 'ROLE_ADMIN' ) ? $this->notificationService::NEW_MESSAGE_FROM_ADMIN : $this->notificationService::NEW_MESSAGE_FROM_USER;
                $this->notificationService->setNotification( $type , $user , $Ticket );

                return $this->handleView($this->view(['status' => 'ok' , 'id' => $TicketMessage->getId()], Response::HTTP_CREATED));
            }
        }

        return $this->handleView($this->view($form->getErrors()));
    }

    /**
     * @Rest\Post("/close")
     * @return Response
     *
     * come dati in POST passare:
     * user_id
     * ticket_id
     */
    public function closeTicketAction(Request $request): Response
    {
        if(is_null( $request->request->get('user_id') )){
            throw new \Exception('In questa versione è importante passare l\'user_id, al posto del token, per operare');
        }

        $user = $this->getUserFromId( $request->request->get('user_id') );
        if(! $user ){
            throw new \Exception('Utente non trovato', 100);
        }

        $em = $this->getDoctrine()->getManager();

        if(! $request->request->get('ticket_id')  ){
            throw new \Exception('Ticket non specificato', 300);
        }

        $repo = $this->getDoctrine()->getRepository(Ticket::class);
        $Ticket = $repo->find( $request->request->get('ticket_id') );

        if( is_null($Ticket) ){
            throw new \Exception('Ticket non specificato', 300);
        }else{
            $this->ticketService->closeTicketFromId($request->request->get('ticket_id'),$user);
        }

        return $this->handleView($this->view(['status' => 'ok'], Response::HTTP_CREATED));
    }

    /**
     * @Rest\Post("/assign")
     * @return Response
     *
     * come dati in POST passare:
     * user_id
     * ticket_id
     */
    public function assignTicketAction(Request $request): Response
    {
        if(is_null( $request->request->get('user_id') )){
            throw new \Exception('In questa versione è importante passare l\'user_id, al posto del token, per operare');
        }

        $user = $this->getUserFromId( $request->request->get('user_id') );
        if(! $user ){
            throw new \Exception('Utente non trovato', 100);
        }

        $em = $this->getDoctrine()->getManager();

        if(! $request->request->get('ticket_id')  ){
            throw new \Exception('Ticket non specificato', 300);
        }

        $repo = $this->getDoctrine()->getRepository(Ticket::class);
        $Ticket = $repo->find( $request->request->get('ticket_id') );

        if( is_null($Ticket) ){
            throw new \Exception('Ticket non specificato', 300);
        }else{
            $this->ticketService->assignTicketFromId($request->request->get('ticket_id'),$user);
        }

        return $this->handleView($this->view(['status' => 'ok'], Response::HTTP_CREATED));
    }

    /**
     * @Rest\Post("/assign_to_other_admin")
     * @return Response
     *
     * come dati in POST passare:
     * from_user_id
     * to_user_id
     * ticket_id
     */
    public function assignToOtherAdminTicketAction(Request $request): Response
    {
        $fromUser = $this->getUserFromId( $request->request->get('from_user_id') );
        if(! $fromUser ){
            throw new \Exception('Utente non trovato', 100);
        }
        $toUser = $this->getUserFromId( $request->request->get('to_user_id') );
        if(! $toUser ){
            throw new \Exception('Utente non trovato', 100);
        }

        if(! $request->request->get('ticket_id')  ){
            throw new \Exception('Ticket non specificato', 300);
        }
        $repo = $this->getDoctrine()->getRepository(Ticket::class);
        $Ticket = $repo->find( $request->request->get('ticket_id') );

        if( is_null($Ticket) ){
            throw new \Exception('Ticket non specificato', 300);
        }else{
            $this->ticketService->setTicketOwner( $Ticket , $fromUser , $toUser );
        }

        return $this->handleView($this->view(['status' => 'ok'], Response::HTTP_CREATED));
    }

    /**
     * Questo metodo dimostrativo va sostituito con sistema di sicurezza ad autenticazione
     */
    private function getUserFromId( $user_id ): ?User
    {
        $repo = $this->getDoctrine()->getManager()->getRepository(User::class);
        $User = $repo->find($user_id);

        return $User;
    }
}