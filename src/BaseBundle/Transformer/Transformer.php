<?php

namespace BaseBundle\Transformer;

use League\Fractal;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 *Transformer that includes the router service for generating routes.
 */
class Transformer extends Fractal\TransformerAbstract
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected $router;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager;
     */
    protected $om;

    /**
     * @var array
     */
    protected $availableIncludes = [];

    /**
     * @var array
     */
    protected $defaultIncludes = [];

    public function __construct(Router $router, ObjectManager $om)
    {
        $this->router = $router;
        $this->om = $om;
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @param string      $route         The name of the route
     * @param mixed       $parameters    An array of parameters
     * @param bool|string $referenceType The type of reference (one of the constants in UrlGeneratorInterface)
     *
     * @return string The generated URL
     *
     * @see UrlGeneratorInterface
     */
    protected function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->router->generate($route, $parameters, $referenceType);
    }

    /**
     * Shortcut to $this->om->getRepository();.
     *
     * @param $entity
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository($entity)
    {
        return $this->om->getRepository($entity);
    }
}
