<?php

namespace Tests\AppBundle\Tests;

use AppBundle\Entity\Category;
use AppBundle\Entity\Product;
use BaseBundle\Tests\AbstractEntityTest;
use Doctrine\Common\Collections\ArrayCollection;
use Hautelook\AliceBundle\Tests\Functional\TestBundle\Entity\Prod;

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
            ['name', ' Category <br>'],
            ['updatedAt', new \Datetime('2015-09-09 00:00:00')],
            ['createdAt', new \Datetime('2015-09-09 00:00:00')],
            ['version', 1],
        ];
    }


    public function testCheckCleanHtmlAndStripTags()
    {
        $class = $this->entityClass();

        $class->exchangeArray($this->dataArrayCollection());

        $classClone = clone $class;

        $this->filter->filterEntity($class);

        $this->assertNotContains("<script>", $class->getName());
        $this->assertNotContains("<p>", $class->getName());

        $this->assertNotEquals($classClone->getName(), $class->getName());
    }


    /**
     * {@inheritdoc}
     */
    public function dataArrayCollection()
    {
        return [
            'id' => 1,
            'name' => 'Category <script><p>',
            'products' => $this->mockCollection(),
            'updatedAt' => new \Datetime('2015-09-09 00:00:00'),
            'createdAt' => new \Datetime('2015-09-09 00:00:00'),
            'version' => 2,
        ];
    }

    protected function mockProduct()
    {
        $module = \Mockery::mock(Product::class)
            ->shouldIgnoreMissing();

        return $module;
    }
}
