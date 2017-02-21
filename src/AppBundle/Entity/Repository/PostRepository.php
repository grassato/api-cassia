<?php

namespace AppBundle\Entity\Repository;

class PostRepository extends AbstractRepository
{
    public function queryLatest()
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT p
                FROM AppBundle:Post p
                WHERE p.publishedAt <= :now
                ORDER BY p.publishedAt DESC
            ')
            ->setParameter('now', new \DateTime())
        ;
    }

    public function getPostQueryBuilder($parameters = null, $execute = true)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e')
            ->from('AppBundle:Post', 'e')
        ;

        if (array_key_exists('sort', $parameters) and !empty($parameters['sort'])) {
            foreach ($parameters['sort'] as $sortConfig) {

                // -> atributos que são permitidos na ordenação
                switch ($sortConfig['field']) {
                    case 'title':
                        $qb->addOrderBy('e.title', $sortConfig['direction']);
                        break;
                    case 'id':
                        $qb->addOrderBy('e.id', $sortConfig['direction']);
                        break;
                }
            }
        }
        return $execute === true ? $qb->getQuery()
            ->getResult()
            : $qb;
    }

    public function findLatest()
    {
        $this->queryLatest()->getResult();
    }
}
