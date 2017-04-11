<?php

namespace AppBundle\Manager;

use AppBundle\Entity\Building;
use BaseBundle\Manager\AbstractManager;
use Doctrine\Common\Persistence\ObjectManager;

class BuildingManager extends AbstractManager
{
    public function __construct(ObjectManager $om)
    {
        parent::__construct($om, Building::class);
    }
}
