<?php

namespace BaseBundle\Pagination;

use BaseBundle\Transformer\CustomSerializer;
use Doctrine\ORM\QueryBuilder;
use League\Fractal\Pagination\PagerfantaPaginatorAdapter;
use League\Fractal\Pagination\PaginatorInterface;
use League\Fractal\Resource\Collection;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use League\Fractal;
use Pagerfanta\Adapter\MongoAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class PaginationFactory
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function createORMCollection(QueryBuilder $qb, Request $request, $routeName, $transform, array $routeParams = array())
    {
        $page = $request->query->get('page', 1);

        $perPage = $request->query->get('perPage', 10);

        if ($perPage < 10) {
            $perPage = 10;
        }

        if ($page < 1) {
            $page = 1;
        }

        $qb->setMaxResults($perPage)
            ->setFirstResult(max($perPage * $perPage, 1))
        ;

        $adapter = new DoctrineORMAdapter($qb);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setCurrentPage($page);
        $pagerfanta->setMaxPerPage($perPage);

        $gerador = function ($page) use ($routeName) {
            return $this->router->generate(
                $routeName,
                array('page' => $page),
                RouterInterface::ABSOLUTE_URL // ABSOLUTE_URL, ABSOLUTE_PATH, RELATIVE_PATH, NETWORK_PATH
            );
        };

        $resourcePaginatorAdapter = new PagerfantaPaginatorAdapter($pagerfanta, $gerador);
        $resource = new Collection($resourcePaginatorAdapter->getPaginator()->getIterator(), $transform);
        $resource->setPaginator($resourcePaginatorAdapter);

        return $resource;
    }

    public function createODMCollection($cursor, Request $request, $routeName, $transform, array $routeParams = array())
    {
        $page = $request->query->get('page', 1);

        $perPage = $request->query->get('perPage', 10);

        if ($perPage < 10) {
            $perPage = 10;
        }

        if ($page < 1) {
            $page = 1;
        }

        $adapter = new MongoAdapter($cursor);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setCurrentPage($page);
        $pagerfanta->setMaxPerPage($perPage);

        $gerador = function ($page) use ($routeName) {
            return $this->router->generate(
                $routeName,
                array('page' => $page),
                RouterInterface::ABSOLUTE_URL // ABSOLUTE_URL, ABSOLUTE_PATH, RELATIVE_PATH, NETWORK_PATH
            );
        };

        $resourcePaginatorAdapter = new PagerfantaPaginatorAdapter($pagerfanta, $gerador);
        $resource = new Collection($resourcePaginatorAdapter->getPaginator()->getIterator(), $transform);
        $resource->setPaginator($resourcePaginatorAdapter);

        return $resource;
    }


    public function collection(Collection $colecao, $index = 'data', array $parsearIncludes = [], array $meta = [])
    {
        $fractal = $this->getFractal();

        if (!empty($parsearIncludes)) {
            foreach ($parsearIncludes as $parsearInclude) {
                $fractal->parseIncludes($parsearInclude);
            }
        }

        if (!empty($meta)) {
            foreach ($meta as $chave => $valor) {
                $colecao->setMetaValue($chave, $valor);
            }
        }

        $colecao->setResourceKey($index);
        $fractal->setSerializer(new CustomSerializer());
        $rootScope = $fractal->createData($colecao);

        return $rootScope->toArray();
    }

    public function createSimpleCollection(QueryBuilder $qb, Request $request, $meta = [])
    {
        $page = $request->query->get('page', 1);

        $perPage = $request->query->get('perPage', 10);

        if ($perPage < 10) {
            $perPage = 10;
        }

        if ($page < 1) {
            $page = 1;
        }

        $qb->setMaxResults($perPage)
            ->setFirstResult(max($perPage * $perPage, 1))
        ;

        $adapter = new DoctrineORMAdapter($qb);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setCurrentPage($page);
        $pagerfanta->setMaxPerPage($perPage);

        $data = ['meta' => ['pagination'=> [] ]];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $data['data'][] = $result;
        }

        $pagination['meta'] = $data['meta'];

        if (count($meta) > 0) {
            $pagination['meta']['metas'] = $meta;
        }
        $pagination['data'] = $data['data'];
        $pagination['meta']['pagination']['total'] = $pagerfanta->getNbResults();
        $pagination['meta']['pagination']['count'] = $pagerfanta->getIterator()->count();
        $pagination['meta']['pagination']['per_page'] = $pagerfanta->getMaxPerPage();
        $pagination['meta']['pagination']['current_page'] = $pagerfanta->getCurrentPage();
        $pagination['meta']['pagination']['total_pages'] = $pagerfanta->getNbPages();

        return $pagination;
    }


    /**
     * @return Fractal\Manager
     */
    public function getFractal()
    {
        return new Fractal\Manager();
    }
}
