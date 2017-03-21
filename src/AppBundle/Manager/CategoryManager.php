<?php

namespace AppBundle\Manager;

use AppBundle\Entity\Category;
use BaseBundle\Manager\AbstractManager;
use Doctrine\Common\Persistence\ObjectManager;

class CategoryManager extends AbstractManager
{
    public function __construct(ObjectManager $om)
    {
        parent::__construct($om, Category::class);
    }
}
