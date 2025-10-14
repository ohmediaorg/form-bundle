<?php

namespace OHMedia\FormBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use OHMedia\FormBundle\Entity\FormField;

/**
 * @method FormField|null find($id, $lockMode = null, $lockVersion = null)
 * @method FormField|null findOneBy(array $criteria, array $orderBy = null)
 * @method FormField[]    findAll()
 * @method FormField[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormFieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormField::class);
    }

    public function save(FormField $formField, bool $flush = false): void
    {
        $this->getEntityManager()->persist($formField);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FormField $formField, bool $flush = false): void
    {
        $this->getEntityManager()->remove($formField);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
