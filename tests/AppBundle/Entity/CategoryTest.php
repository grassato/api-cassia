<?php

namespace Tests\AppBundle\Tests;

use AppBundle\Entity\Category;
use AppBundle\Entity\Product;
use BaseBundle\Tests\AbstractEntityTest;

class CategoryTest extends AbstractEntityTest
{

    /**
     * {@inheritdoc}
     */
    public function entityClass()
    {
        return new Category();
    }

    /**
     * {@inheritdoc}
     */
    public function dataProvider()
    {
        return [
            ['id', 1],
            ['name', ' Refrigerante <br> <script>'],
            ['updatedAt', new \Datetime('2015-09-09 00:00:00')],
            ['createdAt', new \Datetime('2015-09-09 00:00:00')],
            ['version', 1]
        ];
    }


    public function testCheckCleanHtmlAndStripTags()
    {
        $class = $this->entityClass();

        $class->exchangeArray($this->dataArrayCollection());

        $classClone = clone $class;

        $this->getFilter()->filterEntity($class);

        $this->assertNotContains("<script>", $class->getName());
        $this->assertNotContains("<br>", $class->getName());

        $this->assertNotEquals($classClone->getName(), $class->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function dataArrayCollection()
    {
        return [
            'id' => 1,
            'name' => 'Suco <script><p>',
            'products' => $this->mockCollection(),
            'updatedAt' => new \Datetime('2015-09-09 00:00:00'),
            'createdAt' => new \Datetime('2015-09-09 00:00:00'),
            'version' => 2,
        ];
    }
}
