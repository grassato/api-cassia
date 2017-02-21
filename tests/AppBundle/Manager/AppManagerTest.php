<?php
/**
 * Created by PhpStorm.
 * User: diego
 * Date: 25/01/17
 * Time: 10:52
 */

namespace tests\AppBundle\Manager;


use AppBundle\Entity\Post;
use AppBundle\Manager\AppManager;
use BaseBundle\Manager\AbstractManager;
use BaseBundle\Tests\AbstractServiceORMTest;
use Faker\Factory;
use Mockery as m;
use SimpleThings\EntityAudit\AuditConfiguration;
use SimpleThings\EntityAudit\AuditManager;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * @runTestsInSeparateProcesses
 */
class AppManagerTest extends AbstractServiceORMTest
{
    protected $appManager;

    /**
     * @group manager
     */
    public function testInterfaceOfService()
    {
        $this->assertInstanceOf(AbstractManager::class, $this->getAppManager($this->getDefaultEmMock()));
    }

    /**
     * @group manager
     */
    public function testMethodSavePost()
    {
        $faker = Factory::create();

        $data = [
            'title' => $faker->name,
            'slug' => $faker->slug(),
            'content' => $faker->text(100),
        ];

        $accessor = PropertyAccess::createPropertyAccessor();
        $post = new Post();
        foreach ($data as $key => $value) {
            $accessor->setValue($post, $key, $value);
        }

        $postSaved = $this->getAppManager()->save($post);
        $this->assertEquals($postSaved, $post);

        return $postSaved->getId();

    }

    /**
     * @group manager
     * @depends testMethodSavePost
     */
    public function testMethodMergePost($id)
    {
        $faker = Factory::create();

        $data = [
            'title' => $faker->name,
            'slug' => $faker->slug(),
            'content' => $faker->text(100)
        ];

        $accessor = PropertyAccess::createPropertyAccessor();
        $post = new Post();
        foreach ($data as $key => $value) {
            $accessor->setValue($post, $key, $value);
        }

        $postSaved = $this->getAppManager()->merge($post, $id);
        $this->assertEquals($postSaved, $post);

    }

    /**
     * @group manager
     * @depends testMethodSavePost
     */
    public function testMethodFetchPost($id)
    {
        $result = $this->getAppManager()->fetch($id);
        $this->assertInternalType('object', $result);
    }

    /**
     * @group manager
     * @depends testMethodSavePost
     */
    public function testMethodDeletePost($id)
    {
        $result = $this->getAppManager()->delete($id);
        $this->assertTrue($result);

        return $id;
    }

    private function getAppManager($entityManager = null)
    {
        if ($this->appManager === null) {
            $this->appManager = new AppManager($this->getDefaultEmMock() , Post::class);
        }

        return $this->appManager;
    }
}
