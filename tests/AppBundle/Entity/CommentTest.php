<?php
/**
 * Created by PhpStorm.
 * User: diego
 * Date: 25/01/17
 * Time: 10:36
 */

namespace tests\AppBundle\Entity;


use AppBundle\Entity\Comment;
use AppBundle\Entity\Post;
use BaseBundle\Tests\AbstractEntityTest;

class CommentTest extends AbstractEntityTest
{
    /**
     * {@inheritdoc}
     */
    public function entityClass()
    {
        return new Comment();
    }

    /**
     * {@inheritdoc}
     */
    public function dataProvider()
    {
        return [
            ['id', 1],
            ['content', 'user_test'],
            ['post', $this->mockPost()],
            ['deletedAt', new \Datetime('2015-09-09 00:00:00')] ,
            ['updatedAt', new \Datetime('2015-09-09 00:00:00')],
            ['createdAt', new \Datetime('2015-09-09 00:00:00')],
            ['version', 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function dataArrayCollection()
    {
        return [
            'id' => 1,
            'content' => 'salt123456',
            'post' => $this->mockPost(),
            'deletedAt' => new \Datetime('2015-09-09 00:00:00'),
            'updatedAt' => new \Datetime('2015-09-09 00:00:00'),
            'createdAt' => new \Datetime('2015-09-09 00:00:00'),
            'version' => 2,
        ];
    }

    protected function mockPost()
    {
        $module = \Mockery::mock(Post::class)
            ->shouldIgnoreMissing();

        return $module;
    }

}
