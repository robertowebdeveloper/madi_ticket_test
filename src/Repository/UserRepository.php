<?php
namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findAdmin(): ?array
    {
        /**
         * @ToDo: Inserire estensione Doctrine, per poter effettuare una ricerca in DQL sfruttando la possibilità di cercare un singolo elemento in un JSON_ARRAY
         * Attualmente è stato sostituito con il seguente metodo poco efficiente, che filtra gli utente con ruolo "admin" ed esclude gli altri
         */
        $list = $this->findAll();
        $admin_list = [];
        foreach($list as $u){
            if( $u->getRole() == 'ROLE_ADMIN' ){
                $admin_list[] = $u;
            }
        }
        unset($list);

        return $admin_list;
    }
}