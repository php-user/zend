<?php

namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\Article;

class ArticleRepository extends EntityRepository
{
    public function getQueryBuilder($considerIsPublic = true)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('a');
        $qb->from(Article::class, 'AS a');
        if ($considerIsPublic) {
            $qb->where('a.isPublic = 1');
        }
        $qb->orderBy('a.id', 'DESC');

        return $qb ?: false;
    }

    public function getQueryBuilderForCategory($categoryId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('a');
        $qb->from(Article::class, 'AS a');
        $qb->where('a.isPublic = 1');
        $qb->andWhere('a.category = :categoryId');
        $qb->orderBy('a.id', 'DESC');
        $qb->setParameter('categoryId', (int)$categoryId);

        return $qb ?: false;
    }
}
