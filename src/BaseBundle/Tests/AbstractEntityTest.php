<?php

namespace BaseBundle\Tests;

use BaseBundle\Entity\EntityTrait;
use DMS\Filter\Filter;
use DMS\Filter\Filters\Loader\FilterLoader;
use DMS\Filter\Mapping;
use Doctrine\Common\Annotations;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractEntityTest.
 */
abstract class AbstractEntityTest extends TestCase
{
    /**
     * @var \DMS\Filter\Filter
     */
    private $filter;

    /**
     * @var \Prophecy\Prophet
     */
    private $prophet;

    /**
     * @return EntityTrait
     */
    abstract protected function entityClass();

    /**
     * @return array
     */
    abstract protected function dataProvider();

    /**
     * @return array
     */
    abstract protected function dataArrayCollection();

    /**
     * @dataProvider dataProvider
     */
    public function testCheckGetAndSetExpected($attribute, $value)
    {
        $get = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $attribute)));
        $set = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $attribute)));

        $class = $this->entityClass();
        $class->$set($value);

        $this->assertEquals($value, $class->$get());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testCheckMethodsFluid($attribute, $value)
    {
        $set = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $attribute)));

        $class = $this->entityClass();
        $result = $class->$set($value);

        $this->assertClassHasAttribute($attribute, get_class($class));
        $this->assertInstanceOf(get_class($this->entityClass()), $result);
    }

    /**
     * @before
     */
    protected function tearUp()
    {
        $this->filter = new Filter($this->buildMetadataFactory(), new FilterLoader());
    }

    /**
     * @after
     */
    protected function tearDown()
    {
        $refl = new \ReflectionObject($this);
        foreach ($refl->getProperties() as $prop) {
            if (!$prop->isStatic() && 0 !== strpos($prop->getDeclaringClass()->getName(), 'PHPUnit_')) {
                $prop->setAccessible(true);
                $prop->setValue($this, null);
            }
        }
        \Mockery::close();
    }

    /**
     * Check if class exists
     */
    public function testClassExist()
    {
        $this->assertTrue(class_exists(get_class($this->entityClass())));
        $this->assertTrue(trait_exists(EntityTrait::class));
    }

    /**
     * Check if getter and setter array using getArrayCopy, toArray, exchangeArray
     */
    public function testCheckMethodExchangeArraySetFullMethods()
    {
        $entity = $this->entityClass();

        $entity->exchangeArray($this->dataArrayCollection());

        $expected = $entity->getArrayCopy();
        $expectedArray = $entity->toArray();

        $actual = $this->dataArrayCollection();

        unset(
            $expected['createdAt'],
            $expected['updatedAt'],
            $expected['roles'],
            $expectedArray['createdAt'],
            $expectedArray['updatedAt'],
            $expectedArray['roles'],
            $actual['createdAt'],
            $actual['updatedAt'],
            $actual['roles']
        );
        $this->assertEquals($expected, $actual);
        $this->assertEquals($expectedArray, $actual);
    }


    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage setId accept only positive integers greater than zero and
     */
    public function testReturnsExceptionIfNotAnIntegerParameter()
    {
        $class = $this->entityClass();
        for ($i = 0; $i <= 2; ++$i) {
            switch ($i) {
                case 0:
                    $class->setId('hello');
                    break;
                case 1:
                    $class->setId(-1);
                    break;
                case 2:
                    $class->setId(0);
                    break;
            }
        }
    }

    public function testGetMetadataFactory()
    {
        $this->assertInstanceOf('DMS\Filter\Mapping\ClassMetadataFactory', $this->buildMetadataFactory());
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function getLoader()
    {
        return $this->loader;
    }

    protected function buildMetadataFactory()
    {
        $reader = new Annotations\AnnotationReader();
        $loader = new Mapping\Loader\AnnotationLoader($reader);
        $metadataFactory = new Mapping\ClassMetadataFactory($loader);
        return $metadataFactory;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockCollection()
    {
        return $this->getMockBuilder('Doctrine\Common\Collections\ArrayCollection')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
