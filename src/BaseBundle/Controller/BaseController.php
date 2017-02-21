<?php

namespace BaseBundle\Controller;

use BaseBundle\Api\ApiProblem;
use BaseBundle\Api\ApiProblemException;
use BaseBundle\Transformer\CustomSerializer;
use BaseBundle\Transformer\Transformer;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use League\Fractal;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Adapter\MongoAdapter;
use Pagerfanta\Pagerfanta;
use League\Fractal\Pagination\PagerfantaPaginatorAdapter;

abstract class BaseController extends Controller
{
    /**
     * This method should return default manager.
     *
     * @abstract
     *
     * @return ObjectManager
     */
    abstract protected function getManager();


    /**
     * This method should return the entity.
     *
     * @abstract
     *
     * @return getEntiyClass
     */
    abstract protected function getEntiyClass();

    /**
     * This method should return the default transform.
     * @abstract
     * @return Transformer
     */
    abstract protected function getTransformer();
    /**
     * @return Fractal\Manager
     */
    public function getFractal()
    {
        return new Fractal\Manager();
    }

    /**
     * @return \Symfony\Component\Validator\ValidatorInterface
     */
    protected function getValidator()
    {
        return $this->get('validator');
    }

    /**
     * @return \JMS\Serializer\SerializerInterface
     */
    public function getSerializer()
    {
        return $this->get('jms_serializer');
    }

    /**
     * Return repository.
     *
     * @param string $entity namespace
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepository($entity)
    {
        return $this->getDoctrine()->getRepository($entity);
    }

    /**
     * @param string $classe
     *
     * @return array|\JMS\Serializer\scalar|object
     *
     * @throws Exception
     */
    public function unserialize($content, $class, $type = 'json', $context = null)
    {
        try {
            $data = $this->getSerializer()->deserialize(
                $content,
                $class,
                $type,
                $context
            );
        } catch (\Exception $e) {
            $problem = new ApiProblem(400);
            $problem->setTitle("Invalid serializer exception.");
            $problem->setExtra(['filters' => $e]);
            throw new ApiProblemException($problem);
        }

        return $data;
    }

    /**
     * @param $content
     * @return array|\JMS\Serializer\scalar|object
     */
    protected function unserializeClass($content)
    {
        try {
            $class = $this->getEntiyClass();
            $contentJson = $this->unserialize($content, $class, 'json');

            return $contentJson;
        } catch (\Exception $e) {
            $problem = new ApiProblem(400);
            $problem->setTitle("Invalid serializer class exception.");
            $problem->setExtra(['filters' => $e]);
            throw new ApiProblemException($problem);
        }
    }

    /**
     * Apply filters
     * @param $objeto
     */
    public function objectFilter($objeto)
    {
        $filterService = $this->get('dms.filter');

        try {
            $filterService->filterEntity($objeto);
        } catch (\Exception $e) {
            $problem = new ApiProblem(400);
            $problem->setTitle("Invalid filter exception.");
            $problem->setExtra(['filters' => $e]);
            throw new ApiProblemException($problem);
        }
    }

    /**
     * Tranform array in object
     * @param $data
     * @param string $format
     * @return mixed|string
     */
    protected function serialize($data, $groups = [], $format = 'json')
    {
        $context = new SerializationContext();
        $context->setSerializeNull(true);

        $request = $this->get('request_stack')->getCurrentRequest();

        if (count($groups) < 1) {
            $groups = array('Default');
        }
        if ($request->query->get('deep')) {
            $groups[] = 'deep';
        }
        $context->setGroups($groups);

        return $this->container->get('jms_serializer')
            ->serialize($data, $format, $context);
    }

    /**
     * Validate data
     * @param $value
     * @param null $groups
     * @param bool $traverse
     * @param bool $deep
     * @return mixed
     */
    public function validate($value, $constraints = null, $groups = null, $traverse = false, $deep = false)
    {
        $erros = $this->getValidator()->validate($value, $constraints, $groups, $traverse, $deep);

        if (count($erros) > 0) {
            $details = [];
            $problem = new ApiProblem(400);
            $problem->setTitle("Invalid argument exception.");
            foreach ($erros as $e) {
                $details[] = array(
                    'field' => $e->getPropertyPath(),
                    'description' => $e->getMessage()
                );
            }


            $problem->setExtra(['validations' => $details]);
            throw new ApiProblemException($problem);
        }

        return $value;
    }


    public function getFractalItem($item, $callback, $parsearIncludes = null, $exclude = null, array $meta = array())
    {
        $resource = new Item($item, $callback);

        $fractal = $this->getFractal();

        if (!empty($parsearIncludes)) {
            $fractal->parseIncludes($parsearIncludes);
        }

        if (!empty($exclude)) {
            $fractal->parseIncludes($exclude);
        }

        if (!empty($meta)) {
            foreach ($meta as $chave => $valor) {
                $resource->setMetaValue($chave, $valor);
            }
        }

        $fractal->setSerializer(new CustomSerializer());
        $rootScope = $fractal->createData($resource);

        return $rootScope->toArray();
    }


    protected function getFractalCollection(
        $collection,
        $callback,
        $parsearIncludes = null,
        array $meta = array()
    ) {
        $resource = new Collection($collection, $callback);

        $fractal = $this->getFractal();

        if (!empty($parsearIncludes)) {
            $fractal->parseIncludes($parsearIncludes);
        }

        if (!empty($meta)) {
            foreach ($meta as $chave => $valor) {
                $resource->setMetaValue($chave, $valor);
            }
        }

        $fractal->setSerializer(new CustomSerializer());
        $rootScope = $fractal->createData($resource);

        return $rootScope->toArray();
    }

    public function getFractalData($objet, $callback = null, $parsearIncludes = null, array $meta = array())
    {
        if (is_array($objet)) {
            return $this->getFractalCollection($objet, $callback, $parsearIncludes, $meta);
        } else {
            return $this->getFractalItem($objet, $callback, $parsearIncludes, $meta);
        }
    }

    /**
     * Método responsável por parsear o campo de ordenação de string para array.
     *
     * @param string $sort no formato: '-titulo,+criadoEm' para ordenar por: 'ORDER BY titulo DESC, criadoEm ASC'.
     * @return array com os filtros no formato:
     * <code>
     * array(
     *  array('field' => 'titulo', 'direction' => 'DESC'),
     *  array('field' => 'criadoEm', 'direction' => 'ASC'),
     * )
     * </code>
     */
    protected function sanitizaCampoDeOrdenacao($sort = '')
    {
        $ret = [];
        $camposDaOrdenacao = explode(',', $sort);

        foreach ($camposDaOrdenacao as $campo) {
            if (empty($campo)) {
                continue;
            }

            $sort = [];

            if (0 === strpos($campo, '-')) {
                $sort['field'] = trim(substr($campo, 1));
                $sort['direction'] = 'DESC';
            } elseif (0 === strpos($campo, '+')) {
                $sort['field'] = trim(substr($campo, 1));
                $sort['direction'] = 'ASC';
            } else {
                $sort['field'] = trim($campo);
                $sort['direction'] = 'ASC';
            }

            if (!empty($sort)) {
                $ret[] = $sort;
            }
        }

        return $ret;
    }

    protected function createApiResponse($data, $serializerGroups = [], $statusCode = 200)
    {
        $json = $this->serialize($data);

        return new Response($json, $statusCode, array(
            'Content-Type' => 'application/json'
        ));
    }
}
