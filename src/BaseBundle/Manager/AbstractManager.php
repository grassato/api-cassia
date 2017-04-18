<?php

namespace BaseBundle\Manager;

use BaseBundle\Api\ApiProblem;
use BaseBundle\Api\ApiProblemException;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Abstract Manager
 */
abstract class AbstractManager
{
    /**
     * Instance of ObjectManager(em).
     *
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $om;

    /**
     * Instance of EntityRepository
     *
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $repo;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * Entity Class
     *
     * @var \AppBundle\Entity\EntityTrait
     */
    protected $class;

    /**
     * Logger interface
     *
     * @var \Monolog\Logger
     */
    protected $logger;

    public function dispatchEvent($eventName, Event $event)
    {
        $this->getEventDispatcher()->dispatch($eventName, $event);
    }

    public function __construct(ObjectManager $om, $class = null)
    {
        $this->setOm($om);

        if ($class !== null) {
            $this->setClass($class);
            $this->setRepo($om->getRepository($class));
        }
    }

    /**
     * Set the event manager instance.
     *
     * @param EventManagerInterface $events
     *
     * @return self
     */
    public function setEventDispatcher($dispatcher)
    {
        $this->eventDispatcher = $dispatcher;

        return $this;
    }

    /**
     * Retrieve the event manager instance.
     *
     * Lazy-initializes one if none present.
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        if (!$this->eventDispatcher) {
            $this->setEventDispatcher(new EventDispatcher());
        }

        return $this->eventDispatcher;
    }

    public function transformArrayToObject(array $data)
    {
        $class =  $this->getClass();
        $object = new $class();
        $object->exchangeArray($data);

        return $object;
    }

    /**
     * @param       $data
     * @param       $id
     * @param array $excludes
     */
    public function mergeObject($data, $id, $excludes = [])
    {

        $dafaultExcludes = ['id', 'createdAt', 'updatedAt', 'version'];
        $dafaultExcludes = array_merge($dafaultExcludes, $excludes);

        $accessor = PropertyAccess::createPropertyAccessor();

        $entity = $this->fetch($id);

        if($entity === NULL){

            return ApiProblemException::throw("Object ". $this->getClass(). " is not exists,", 400);
        }

        foreach ($data as $key => $value) {

            if (!in_array($key, $dafaultExcludes)) {


                if ($value !== NULL) {

                    $accessor->setValue($entity, $key, $value);

                }

            }
        }

        return $entity;
    }

    public function merge($object)
    {
        $this->getOm()->merge($object);

        $this->getOm()->flush();

        return $object;
    }

    public function save($data)
    {
        $this->getOm()->persist($data);
        $this->getOm()->flush();

        return $data;
    }

    public function delete($data, $flush = true)
    {
        $entity = $data;

        if (is_numeric($entity)) {

            $entity = $this->fetch($data);
        }

        if ($entity instanceof \Doctrine\ORM\Proxy\Proxy) {
            return false;
        }

        $this->getOm()->remove($entity);

        if ($flush)
            $this->getOm()->flush();

        return true;
    }

    /**
     * @param $id
     * @return \Exception|null|object
     */
    public function fetch($id)
    {
        try {
            $ret = $this->getRepo($this->getClass())
                ->find(['id' => (int) $id]);
        } catch (\Exception $e) {
            return $e;
        }

        return $ret;
    }


    /**
     * Populate multiple association objects
     *
     * @param $object
     *
     * @return mixed
     *
     */
    private function getAssociationMultipleObjects(&$object, $field)
    {

        $setMethod = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
        $getMethod = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
        $addMethod = 'add' . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));

        $class = get_class($object);
        $subClass           = $this->getOm()
            ->getClassMetadata($class)
            ->getAssociationTargetClass($field);

        $identify               = $this->getOm()
            ->getClassMetadata($subClass)
            ->getIdentifierFieldNames();



        if (!method_exists($object, $addMethod) ) {

            return ApiProblemException::throw("Attempted to call an undefined method named ($class::$addMethod).", 400);
        }

        if (!method_exists($object, $getMethod) ){

            return ApiProblemException::throw("Attempted to call an undefined method named ($class::$getMethod) .", 400);
        }


        $idIdentify = [];
        $iterate = $object->$getMethod();

        if (count($iterate) > 0) {

            foreach ($iterate as $elements) {

                if (is_object($elements)) {

                    $elements = $elements->getId();
                }

                $idIdentify[] = $elements;

            }


            $object->$setMethod(NULL);


            $classCollectionValue = $this->getOm()
                ->getRepository($subClass)
                ->findBy([$identify[0] => $idIdentify], [$identify[0] => 'DESC']);

            $object->$addMethod($classCollectionValue);

        }

    }

    /**
     * Populate single association objects
     *
     * @param $object
     *
     * @return mixed
     *
     */
    private function getAssociationSingleObject(&$object, $field)
    {
        $setMethod = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
        $getMethod = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));

        $class = get_class($object);
        $subClass           = $this->getOm()
            ->getClassMetadata($class)
            ->getAssociationTargetClass($field);

        $identify               = $this->getOm()
            ->getClassMetadata($subClass)
            ->getIdentifierFieldNames();

        if (!method_exists($object, $setMethod) ) {

            return ApiProblemException::throw("Method ($class::$setMethod) is not exists.", 417);
        }

        if (!method_exists($object, $getMethod) ){

            return ApiProblemException::throw("Method ($class::$getMethod) is not exists.", 417);
        }

        $element = $object->$getMethod();

        if (is_object($element)) {

            $element = $element->getId();
        }

        $object->$setMethod(NULL);

        $classValue = $this->getOm()
            ->getRepository($subClass)
            ->findOneBy([$identify[0] => $element]);

        if($classValue === NULL){

            return ApiProblemException::throw("Object $subClass($element) is not exists.", 422);
        }

        $object->$setMethod($classValue);

    }

    /**
     * Check class has sub entity and popule with real object
     *
     * @param $object
     *
     * @return mixed
     *
     */
    public function getAssociationTargetObject($object)
    {
        $classMetadata = $this->getOm()->getClassMetadata(get_class($object));

        $fieldMapping = $classMetadata->getAssociationNames();

        foreach ($fieldMapping as $field) {

            $associationMapping = $this->getOm()
                ->getClassMetadata(get_class($object))
                ->getAssociationMappings();

            $associationTypeBoolean = $this->getOm()
                ->getClassMetadata(get_class($object))
                ->isCollectionValuedAssociation($field);

            if ($associationTypeBoolean && $associationMapping[$field]['type'] === 8) {

                $this->getAssociationMultipleObjects($object, $field);

            }else {

                $this->getAssociationSingleObject($object, $field);

            }
        }

        return $object;
    }

    /**
     * @return ObjectManager
     */
    public function getOm()
    {
        return $this->om;
    }

    /**
     * @param ObjectManager $om
     */
    public function setOm($om)
    {
        $this->om = $om;

        return $this;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepo()
    {
        return $this->repo;
    }

    /**
     * @param \Doctrine\ORM\EntityRepository $repo
     */
    public function setRepo($repo)
    {
        $this->repo = $repo;

        return $this;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return \Monolog\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param \Monolog\Logger $logger
     * @return AbstractManager
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
        return $this;
    }
}
