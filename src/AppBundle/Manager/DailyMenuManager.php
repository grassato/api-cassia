<?php

namespace AppBundle\Manager;

use AppBundle\Entity\DailyMenu;
use BaseBundle\Manager\AbstractManager;
use Doctrine\Common\Persistence\ObjectManager;

class DailyMenuManager extends AbstractManager
{
    public function __construct(ObjectManager $om)
    {
        parent::__construct($om, DailyMenu::class);
    }
}
