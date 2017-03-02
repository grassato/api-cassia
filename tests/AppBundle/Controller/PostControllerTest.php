<?php
/**
 * Created by PhpStorm.
 * User: diego
 * Date: 25/01/17
 * Time: 10:52
 */

namespace tests\AppBundle\Controller;

use AppBundle\Entity\Post;
use BaseBundle\Tests\ApiTestCase;
use Faker\Factory;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * @runTestsInSeparateProcesses
 */
class PostControllerTest extends ApiTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->createUser('diego');
    }

    /**
     * @group web
     */
    protected function createPost(array $data = null)
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $post = new Post();

        if (empty($data)) {
            $data = [
                'title' => 'Post',
                'slug' => 'object_orienter',
                'content' => 'content test dev!'
            ];
        }

        foreach ($data as $key => $value) {
            $accessor->setValue($post, $key, $value);
        }

        $this->getEntityManager()->persist($post);
        $this->getEntityManager()->flush();

        return $post;
    }

    /**
     * @group web
     */
    public function testPOST()
    {
        $faker = Factory::create();

        $data = [
            'title' => $faker->name,
            'slug' => $faker->slug(),
            'content' => $faker->text(100)
        ];

        $response = $this->client->post('/api/v1/en/post', [
            'body' => json_encode($data)
        ]);
        $finishedData = json_decode($response->getBody(true), true);
        $this->testCREATEandPUT($response, $data, $finishedData['id']);
    }

    /**
     * @group web
     */
    public function testGETbyId()
    {
        $post = $this->createPost();

        $response = $this->client->get('/api/v1/en/post/' . $post->getId());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, $post->getId());
        $this->asserter()->assertResponsePropertyContains($response,  'title', 'Post');
    }

    /**
     * @group web
     */
    public function testGETALL()
    {
        $this->createPost();

        $faker = Factory::create();

        $data2 = [
            'title' => $faker->name,
            'slug' => $faker->slug(),
            'content' => $faker->text(100)
        ];

        $this->createPost($data2);

        $response = $this->client->get('/api/v1/en/post');

        $data = json_decode((string)$response->getBody());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertCount(2, $data);
        $this->assertInternalType('array', $data);
    }

    /**
     * @group web
     */
    public function testPUTPost()
    {
        $post = $this->createPost();

        $faker = Factory::create();

        $data = [
            'title' => $faker->name,
            'slug' => $faker->slug(),
            'content' => $faker->text(100)
        ];


        $response = $this->client->put('/api/v1/en/post/' . $post->getId(), [
            'body' => json_encode($data)
        ]);


        $this->testCREATEandPUT($response, $data, $post->getId());
    }


    private function testCREATEandPUT($response, $data,  $id)
    {
        $this->assertEquals(200, $response->getStatusCode());
        $finishedData = json_decode($response->getBody(true), true);

        // Get last revision
        $revison = $this->getRevision(Post::class, $id);

        // Is object ?
        $this->assertInternalType('object', $revison);

        foreach ($data as $key => $value) {

            // This response return key inseted/updated
            $this->asserter()->assertResponsePropertyEquals($response, $key, $data[$key]);

            // This response has key
            $this->assertArrayHasKey($key, $finishedData);

            //  Revision has same key of the current object
            $this->assertObjectHasAttribute($key, $revison);

            $set = 'get'.str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));

            // Revision has same value of the current object
            $this->assertEquals($value, $revison->$set());
        }
    }

    /**
     * @group web
     */
    public function testDELETEPost()
    {
        $post = $this->createPost();

        $response = $this->client->delete('/api/v1/en/post/' . $post->getId());

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @group web
     */
    public function test404Exception()
    {
        $response = $this->client->get('/api/post/fake');

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type')[0]);
        $this->asserter()->assertResponsePropertyEquals($response, 'title', 'Not Found');
    }
}
