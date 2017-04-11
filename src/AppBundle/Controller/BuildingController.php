<?php

namespace AppBundle\Controller;


use AppBundle\Entity\Building;
use AppBundle\Manager\BuildingManager;
use BaseBundle\Api\ApiProblemException;
use BaseBundle\Controller\BaseController;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Hateoas\HateoasBuilder;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Hateoas\Representation\PaginatedRepresentation;
use Hautelook\AliceBundle\Tests\Functional\TestBundle\Entity\Prod;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class BuildingController extends BaseController
{
    /**
     * @throws ApiProblemException
     * @ApiDoc(
     *  section="Api Building",
     *  description="Fetch a resource.",
     *  authentication=true,
     *  parameters={
     *  },
     *  requirements={
     *  },
     *  statusCodes={
     *    200="Successful"
     *  }
     * )
     * @FOSRest\Get("/buildings/{id}", name="_building")
     * @ParamConverter("Building", class="AppBundle:Building")
     * @FOSRest\View(serializerGroups={"identify", "building-details"})
     */
    public function getAction(Building $building)
    {
        return $building;
    }

    /**
     * @throws ApiProblemException
     *
     * @return array
     * @ApiDoc(
     *  section="Api Building",
     *  description="Fetch all or a subset of resources.",
     *  authentication=true,
     *  parameters={
     *  },
     *  requirements={
     *  },
     * statusCodes={
     *         200="Returned when successful",
     *         403="Returned when the user is not authorized to say hello",
     *         404={
     *           "Returned when the user is not found",
     *           "Returned when something else is not found"
     *         },
     *         401="Returned when the user is not authenticated",
     *         500="Returned when some internal server error"
     *   }
     * )
     * @FOSRest\Get("/buildings", name="_buildings")
     */
    public function cgetAction(Request $request)
    {
        $filtro['sort'] = $this->sanitizeDirectionFields($request->query->get('sort', ''));
        $qb = $this->getManager()->getRepo()->getQueryBuilder($filtro, false);

        $paginador = $this->get('api_pagination_factory');

        $collection = $paginador->createSimpleCollection($qb, $request);

        $groups = ['identify','building-summary'];

        $response = $this->createApiResponse($collection, $groups);

        return $response;
    }

    /**
     * This method should return the manager.
     *
     * @abstract
     *
     * @return BuildingManager
     */
    public function getManager()
    {
        return $this->get('building.manager');
    }

    /**
     * This method should return the manager.
     *
     * @abstract
     *
     * @return Building
     */
    protected function getEntityClass()
    {
        return Building::class;
    }

    /**
     * @throws ApiProblemException
     * Create a resource.
     *
     * @return array
     * @ApiDoc(
     *  section="Api Building",
     *  description="Create a resource",
     *  authentication=true,
     *  parameters={
     *  },
     *  requirements={
     *  },
     * statusCodes={
     *         200="Returned when successful",
     *         403="Returned when the user is not authorized to say hello",
     *         404={
     *           "Returned when the user is not found",
     *           "Returned when something else is not found"
     *         },
     *         401="Returned when the user is not authenticated",
     *         500="Returned when some internal server error"
     *   }
     * )
     * @FOSRest\Post("/buildings", name="_buildings")
     * @FOSRest\View(serializerGroups={"identify", "building-details"})
     */
    public function postAction(Request $request)
    {

      $entityObject = $this->unserializeClass($request->getContent());

      $this->objectFilter($entityObject);
      $this->validate($entityObject);

      $post = $this->getManager()->save($entityObject);

      return $post;
    }

    /**
     * @throws ApiProblemException
     *
     * @return array
     * @ApiDoc(
     *  section="Api Building",
     *  description="Update a resource.",
     *  authentication=true,
     *  parameters={
     *  },
     *  requirements={
     *  },
     * statusCodes={
     *         200="Returned when successful",
     *         403="Returned when the user is not authorized to say hello",
     *         404={
     *           "Returned when the user is not found",
     *           "Returned when something else is not found"
     *         },
     *         401="Returned when the user is not authenticated",
     *         500="Returned when some internal server error"
     *   }
     * )
     * @FOSRest\Put("/buildings/{id}", name="_buildings")
     * @FOSRest\View(serializerGroups={"identify", "building-details"})
     */
    public function putAction($id, Request $request)
    {
        $entityObject = $this->unserializeClass($request->getContent());

        $this->objectFilter($entityObject);
        $this->validate($entityObject);
        $object = $this->getManager()->mergeObject($entityObject->toArray(), $id);

        $this->getManager()->merge($object);

        return $object;
    }
}
