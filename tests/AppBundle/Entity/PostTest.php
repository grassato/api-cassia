<?php

namespace Tests\AppBundle\Tests;


use AppBundle\Entity\Post;
use BaseBundle\Tests\AbstractEntityTest;

class PostTest extends AbstractEntityTest
{

    /**
     * {@inheritdoc}
     */
    public function entityClass()
    {
        return new Post();
    }

    /**
     * {@inheritdoc}
     */
    public function dataProvider()
    {
        return [
            ['id', 1],
            ['title', 'user_test'],
            ['content', 'user_test'],
            ['slug', 'user_test'],
            ['comments', $this->mockCollection()],
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
            'title' => 'user_test',
            'slug' => 'user_test',
            'content' => 'salt123456',
            'comments' => $this->mockCollection(),
            'deletedAt' => new \Datetime('2015-09-09 00:00:00'),
            'updatedAt' => new \Datetime('2015-09-09 00:00:00'),
            'createdAt' => new \Datetime('2015-09-09 00:00:00'),
            'version' => 2,
        ];
    }

}
