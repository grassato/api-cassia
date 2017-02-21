<?php

namespace Tests\AppBundle\Tests;


use AppBundle\Entity\Post;
use AppBundle\Entity\User;
use BaseBundle\Tests\AbstractEntityTest;

class UserTest extends AbstractEntityTest
{

    /**
     * {@inheritdoc}
     */
    public function entityClass()
    {
        return new User();
    }

    /**
     * {@inheritdoc}
     */
    public function dataProvider()
    {
        return [
            ['id', 1],
            ['username', 'username_test'],
            ['password', 'user_password'],
            ['email', 'user_email@email.com'],
            ['roles', ['ROLE_USER', 'ROLE_ADMIN']],
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
            'username' => 'username_test',
            'password' => 'user_password',
            'email' => 'user_email@email.com',
            'roles' => ['ROLE_USER', 'ROLE_ADMIN'],
            'updatedAt' => new \Datetime('2015-09-09 00:00:00'),
            'createdAt' => new \Datetime('2015-09-09 00:00:00'),
            'version' => 2,
        ];
    }

}
