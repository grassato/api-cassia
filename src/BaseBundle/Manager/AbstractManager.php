<?php

namespace BaseBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

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

    public function getObject(array $data)
    {
        $class =  $this->getClass();
        $object = new $class();
        $object->exchangeArray($data);

        return $object;
    }

    public function merge($data, $id = 0)
    {
        if (is_numeric($id) && $id > 0) {
            $this->getOm()->merge($data);
        }

        $this->getOm()->flush();


        return $data;
    }

    public function save($data)
    {
        $this->getOm()->persist($data);
        $this->getOm()->flush();

        return $data;
    }

    public function delete($id)
    {
        try {
            $entity = $this->fetch($id);

            if ($entity instanceof \Doctrine\ORM\Proxy\Proxy) {
                return false;
            }

            $this->getOm()->remove($entity);
            $this->getOm()->flush();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

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
