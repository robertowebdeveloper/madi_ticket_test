<?php
namespace App\Repository;

use App\Entity\Ticket;
use App\Entity\TicketMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TicketMessageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TicketMessage::class);
    }

    public function findList( Ticket $Ticket ): ?array
    {
        $qb = $this->createQueryBuilder('tm');
        $q = $qb
            ->where('tm.Ticket = :Ticket')
            ->setParameter('Ticket' , $Ticket)
            ->andWhere('tm.deletedAt IS NULL')
            ->orderBy('tm.createdAt', 'DESC')
            ->getQuery()
        ;

        return $q->getResult();
    }
}