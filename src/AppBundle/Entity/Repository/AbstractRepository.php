<?php
/**
 * Created by PhpStorm.
 * User: diego
 * Date: 14/01/17
 * Time: 23:34
 */

namespace AppBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class AbstractRepository extends EntityRepository
{
    public function getQuery($cache = 'cache')
    {
        $cacheId = md5($cache);
        $qb = $this->createQueryBuilder('u');
        $qb->getQuery()
            ->useQueryCache(true)
            ->useResultCache(true, 3600, $cacheId);

        return $qb;
    }

    public function findAll()
    {
        try {
            $qb = $this->createQueryBuilder('e');
            $result = $qb->getQuery()
                ->useQueryCache(true)
                //->useResultCache(true, 3600, $this->getClassName())
                ->getResult();

            return  $result;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getQueryBuilder($parameters = null, $execute = true)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e')
            ->from($this->getClassName(), 'e')
        ;

        if (array_key_exists('sort', $parameters) and !empty($parameters['sort'])) {
            foreach ($parameters['sort'] as $sortConfig) {

                $qb->addOrderBy("e.${sortConfig['field']}", $sortConfig['direction']);
            }
        }
        return $execute === true ? $qb->getQuery()
            ->getResult()
            : $qb;
    }
}
