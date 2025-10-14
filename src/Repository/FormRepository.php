<?php

namespace OHMedia\FormBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use OHMedia\FormBundle\Entity\Form;
use OHMedia\TimezoneBundle\Util\DateTimeUtil;
use OHMedia\WysiwygBundle\Repository\WysiwygRepositoryInterface;

/**
 * @method Form|null find($id, $lockMode = null, $lockVersion = null)
 * @method Form|null findOneBy(array $criteria, array $orderBy = null)
 * @method Form[]    findAll()
 * @method Form[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormRepository extends ServiceEntityRepository implements WysiwygRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Form::class);
    }

    public function save(Form $form, bool $flush = false): void
    {
        $this->getEntityManager()->persist($form);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Form $form, bool $flush = false): void
    {
        $this->getEntityManager()->remove($form);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function createPublishedQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder
    {
        return $this->createQueryBuilder($alias, $indexBy)
            ->andWhere($alias.'.published_at IS NOT NULL')
            ->andWhere($alias.'.published_at <= :now')
            ->setParameter('now', DateTimeUtil::getDateTimeUtc())
            ->orderBy($alias.'.published_at', 'DESC');
    }

    public function getShortcodeQueryBuilder(string $shortcode): QueryBuilder
    {
        return $this->createQueryBuilder('f')
            ->where('f.description LIKE :shortcode')
            ->setParameter('shortcode', '%'.$shortcode.'%');
    }

    public function getShortcodeRoute(): string
    {
        return 'form_view';
    }

    public function getShortcodeRouteParams(mixed $entity): array
    {
        return ['id' => $entity->getId()];
    }

    public function getShortcodeHeading(): string
    {
        return 'Forms';
    }

    public function getShortcodeLinkText(mixed $entity): string
    {
        return sprintf(
            '%s (ID:%s)',
            (string) $entity,
            $entity->getId(),
        );
    }
}
