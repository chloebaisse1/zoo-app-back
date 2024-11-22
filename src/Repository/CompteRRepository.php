<?php

namespace App\Repository;

use App\Entity\CompteR;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CompteRRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompteR::class);
    }

    /**
     * Recherche des comptes-rendus par nom et/ou date.
     *
     * @param string|null $nom Le nom de l'animal à rechercher.
     * @param \DateTime|null $date La date du compte-rendu.
     * @return CompteR[] Retourne un tableau d'objets CompteR correspondant aux critères.
     */
    public function findByFilters(?string $nom = null, ?\DateTime $date = null): array
    {
        $qb = $this->createQueryBuilder('c');

        if ($nom) {
            $qb->andWhere('c.nom LIKE :nom')
               ->setParameter('nom', '%' . $nom . '%');
        }

        if ($date) {
            $qb->andWhere('DATE(c.date) = :date')
               ->setParameter('date', $date->format('Y-m-d'));
        }

        return $qb->getQuery()->getResult();
    }
}