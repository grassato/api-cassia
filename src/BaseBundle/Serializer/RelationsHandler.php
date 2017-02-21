<?php
/**
 * Created by PhpStorm.
 * User: diego
 * Date: 05/02/17
 * Time: 23:47
 */

namespace BaseBundle\Serializer;

use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\Context;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;

final class RelationsHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * RelationsHandler constructor.
     *
     * @param EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function serializeRelation(JsonSerializationVisitor $visitor, $relation, array $type, Context $context)
    {
        $className = isset($type['params'][0]['name']) ? $type['params'][0]['name'] : null;
        if ($relation instanceof \Traversable) {
            $relation = iterator_to_array($relation);
        }

        return $relation;
        if (is_array($relation)) {
            return array_map([$this, 'getSingleEntityRelation'], $relation);
        }

        return $this->getSingleEntityRelation($relation);
    }

    public function deserializeRelation(JsonDeserializationVisitor $visitor, $relation, array $type, Context $context)
    {
        $className = isset($type['params'][0]['name']) ? $type['params'][0]['name'] : null;

        if (!class_exists($className, false)) {
            throw new \InvalidArgumentException('Class name should be explicitly set for deserialization');
        }

        $objects = [];
        foreach ($relation as $idSet) {
            if (!is_array($idSet)) {
                echo $idSet;
                exit;
                return $this->manager->getReference($className, $idSet);
            }

            foreach ($idSet as $id) {
                echo $id;
                exit;
                $objects[] = $this->manager->getReference($className, $id);
            }
        }
        return $objects;
    }

    /**
     * @param $relation
     *
     * @return array|mixed
     */
    protected function getSingleEntityRelation($relation)
    {
        $metadata = $this->manager->getClassMetadata(get_class($relation));

        $ids = $metadata->getIdentifierValues($relation);
        if (!$metadata->isIdentifierComposite) {
            $ids = array_shift($ids);
        }

        return $ids;
    }
}
