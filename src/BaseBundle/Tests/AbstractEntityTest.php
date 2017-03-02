<?php
namespace BaseBundle\Tests;

use BaseBundle\Entity\EntityTrait;
use DMS\Filter\Filter;
use DMS\Filter\Filters\Loader\FilterLoader;
use DMS\Filter\Mapping;
use Doctrine\Common\Annotations;

/**
 * Class AbstractEntityTest.
 */
abstract class AbstractEntityTest extends \PHPUnit_Framework_TestCase
{
    protected $filter;
    protected $loader;

    abstract public function entityClass();

    public function testClassExist()
    {
        $this->assertTrue(class_exists(get_class($this->entityClass())));
        $this->assertTrue(trait_exists(EntityTrait::class));
    }

    /**
     * @return array
     */
    abstract public function dataProvider();

    /**
     * @return array
     */
    abstract public function dataArrayCollection();

    /**
     * @var \Prophecy\Prophet
     */
    public $prophet;

    /**
     * @dataProvider dataProvider
     */
    public function testCheckGetAndSetExpected($attribute, $value)
    {
        $get = 'get'.str_replace(' ', '', ucwords(str_replace('_', ' ', $attribute)));
        $set = 'set'.str_replace(' ', '', ucwords(str_replace('_', ' ', $attribute)));

        $class = $this->entityClass();
        $class->$set($value);

        $this->assertEquals($value, $class->$get());
    }

    protected function setup()
    {
        $this->filter = new Filter($this->buildMetadataFactory(), new FilterLoader());
        parent::setup();
    }

    /**
     * @dataProvider dataProvider
     */
    public function testCheckMethodsFluid($attribute, $value)
    {
        $set = 'set'.str_replace(' ', '', ucwords(str_replace('_', ' ', $attribute)));

        $class = $this->entityClass();
        $result = $class->$set($value);

        $this->assertClassHasAttribute($attribute, get_class($class));
        $this->assertInstanceOf(get_class($this->entityClass()), $result);
    }

    public function testCheckMethodExchangeArraySetFullMethods()
    {
        $entity = $this->entityClass();

        $entity->exchangeArray($this->dataArrayCollection());
//        $accessor = PropertyAccess::createPropertyAccessor();
//        foreach ($this->dataArrayCollection() as $key => $value) {
//            $accessor->setValue($entity, $key, $value);
//        }

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

    protected function mockCollection()
    {
        return $this->getMockBuilder('Doctrine\Common\Collections\ArrayCollection')
            ->disableOriginalConstructor()
            ->getMock();
    }

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
        parent::tearDown();
    }

    protected function buildMetadataFactory()
    {
        $reader = new Annotations\AnnotationReader();
        $loader = new Mapping\Loader\AnnotationLoader($reader);
        $this->loader = $loader;
        $metadataFactory = new Mapping\ClassMetadataFactory($loader);
        return $metadataFactory;
    }

    public function testGetMetadataFactory()
    {
        $this->assertInstanceOf('DMS\Filter\Mapping\ClassMetadataFactory', $this->buildMetadataFactory());
    }
}
