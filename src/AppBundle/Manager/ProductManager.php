<?php

namespace AppBundle\Manager;

use AppBundle\Entity\Product;
use BaseBundle\Manager\AbstractManager;
use Doctrine\Common\Persistence\ObjectManager;

class ProductManager extends AbstractManager
{
    public function __construct(ObjectManager $om)
    {
        parent::__construct($om, Product::class);
    }

    /**
     * @param \AppBundle\Entity\Product $project
     *
     * Remove tags
     */
    public function removeOldTags(Product $project) {

        foreach ($project->getTags() as $tag) {

            $project->removeTag($tag);
            $this->getOm()->persist($project);
        }

        $this->getOm()->flush();
    }

}
