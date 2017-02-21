<?php

namespace AppBundle\Manager;

use AppBundle\Entity\Comment;
use AppBundle\Entity\Post;
use BaseBundle\Manager\AbstractManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\LockMode;
use Zend\Hydrator\Reflection;

class AppManager extends AbstractManager
{
    public function __construct(ObjectManager $om)
    {
        parent::__construct($om, Post::class);
    }

    public function save($data)
    {
        $entity = null;
        $this->getOm()->persist($data);

        if ($data->getComments() instanceof ArrayCollection) {
            foreach ($data->getComments() as $comment) {
                $comment->setPost($data);
                $this->getOm()->persist($comment);
            }
        }
        $this->getOm()->flush();

        return $data;
    }

    public function merge($data, $id = 0)
    {
        if (is_numeric($id) && $id > 0) {
            $this->getOm()->merge($data);
        }

        $this->getOm()->flush();


        return $data;
    }

    public function main()
    {
        $this->getLogger()->info('ola');
        $this->getLogger()->debug('ola 2');
        return [1, 2, 3 ,4 ,5 ,6];
    }
}
