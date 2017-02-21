<?php
/**
 * Created by PhpStorm.
 * User: diego
 * Date: 24/06/16
 * Time: 00:30.
 */
namespace BaseBundle\DataFixtures\ORM;

use Hautelook\AliceBundle\Doctrine\DataFixtures\AbstractLoader;

class AppFixtures extends AbstractLoader
{
    /**
     * {@inheritdoc}
     */
    public function getFixtures()
    {
        return  [
            __DIR__.'/post.yml',
            __DIR__.'/comment.yml',
        ];
    }
}
