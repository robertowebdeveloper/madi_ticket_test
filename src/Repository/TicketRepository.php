<?php
namespace App\Repository;

use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TicketRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    public function findFromAdmin(): ?array
    {
        return $this->findFrom('admin');
    }

    public function findFromUser( $User ): ?array
    {
        return $this->findFrom('user' , $User);
    }

    private function findFrom( string $w , User $User = null ): ?array
    {
        $qb = $this->createQueryBuilder('t');

        if( $w == 'user' ){
            $qb->andWhere('t.openFromUser = :User')
                ->setParameter('User' , $User)
            ;
        }
        $q = $qb
            ->andWhere('t.deletedAt IS NULL')
            ->orderBy('t.dateLastUpdatedAt', 'DESC')
            ->getQuery()
        ;

        return $q->getResult();
    }
}