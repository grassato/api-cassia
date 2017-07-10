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
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class PostControllerTest extends ApiTestCase
{
    /**
     * @before
     */
    protected function up()
    {
        parent::tearUp();

        $this->createUser('diego');
    }


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
        $finishedData = json_decode($response->getBody()->getContents(), true);
        $this->creatAndPUT($response, $data, $finishedData['id']);
    }

    /**
     * @group web
     */
    public function testGETbyId()
    {
        $post = $this->createPost();

        $response = $this->client->get('/api/v1/en/post/' . $post->getId());

        $this->asserter()->assertResponsePropertyContains($response, 'title', 'Post');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, $post->getId());
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

        $this->assertBasicStructure($response);

        $this->assertEquals(200, $response->getStatusCode());
    }

    private function assertBasicStructure($response)
    {
        $data = json_decode($response->getBody()->getContents(), true);

        // Elements: meta, data
        $this->assertCount(2, $data['data']);
        $this->assertInternalType('array', $data);

        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('meta', $data);
        $this->assertArrayHasKey('pagination', $data['meta']);
        $pagination = $data['meta']['pagination'];
        $this->assertEquals(2, $pagination['total']);
        $this->assertEquals(2, $pagination['count']);
        $this->assertEquals(1, $pagination['current_page']);
        $this->assertEquals(1, $pagination['total_pages']);
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

        $this->creatAndPUT($response, $data, $post->getId());
    }


    private function creatAndPUT($response, $data,  $id)
    {
        $this->assertEquals(200, $response->getStatusCode());

        foreach ($data as $key => $value) {
            $this->asserter()->assertResponsePropertyEquals($response, $key, $data[$key]);
            $this->asserter()->assertResponsePropertyContains($response, $key, $value);
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
