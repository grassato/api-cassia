<?php
/**
 * Created by PhpStorm.
 * User: diego
 * Date: 22/01/17
 * Time: 20:03
 */

namespace BaseBundle\Controller;

use BaseBundle\Api\ApiProblem;
use BaseBundle\Api\ApiProblemException;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

abstract class CrudController extends BaseController implements ClassResourceInterface
{

    /**
     * @throws ApiProblemException
     * @ApiDoc(
     *  section="Api Demo",
     *  description="Fetch a resource.",
     *  parameters={
     *  },
     *  requirements={
     *  },
     *  statusCodes={
     *    200="Successful"
     *  }
     * )
     */
    public function getAction($id)
    {
        $post = $this->getManager()->fetch($id);

        if ($post == null) {
            $problem = new ApiProblem(400);
            $problem->setTitle("Object not found!");
            $problem->setExtra(['entity' =>  $this->getEntiyClass(), 'id' => $id]);
            throw new ApiProblemException($problem);
        }
        $this->objectFilter($post);
        return $this->createApiResponse($post);
    }
    /**
     * @throws ApiProblemException
     * Create a resource.
     *
     * @return array
     * @ApiDoc(
     *  section="Api Demo",
     *  description="Create a resource",
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
     */
    public function postAction(Request $request)
    {
        $entityObjet = $this->unserializeClass($request->getContent());

        $this->objectFilter($entityObjet);
        $this->validate($entityObjet);
        $post = $this->getManager()->save($entityObjet);

        return $this->createApiResponse($post);
    }

    /**
     * @throws ApiProblemException
     *
     * @return array
     * @ApiDoc(
     *  section="Api Demo",
     *  description="Update a resource.",
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
     */
    public function putAction($id, Request $request)
    {
        $entityObjet = $this->unserializeClass($request->getContent());
        $this->objectFilter($entityObjet);
        $this->validate($entityObjet);

        $entity = $this->getManager()->fetch($id);
        $entity->exchangeArray($entityObjet->toArray(), $entity);

        $post = $this->getManager()->merge($entity, $id);

        return $this->createApiResponse($post);
    }

    /**
     * @throws ApiProblemException
     *
     * @return array
     * @ApiDoc(
     *  section="Api Demo",
     *  description=" Delete a resource.",
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
     */
    public function deleteAction($id)
    {
        return $this->getManager()->delete($id);
    }

    /**
     * @throws ApiProblemException
     *
     * @return array
     * @ApiDoc(
     *  section="Api Demo",
     *  description="Fetch all or a subset of resources.",
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
     */
    public function cgetAction(Request $request, ParamFetcherInterface $paramFetcher)
    {
        $post = $this->getManager()->getRepo()->getPostQueryBuilder();

        $paginatedCollection = $this->get('api_pagination_factory')
            ->createCollection($post, $request, 'cget_post');

        if ($post == null) {
            $problem = new ApiProblem(400);
            $problem->setTitle("Object not found!");
            throw new ApiProblemException($problem);
        }

        return $paginatedCollection;
    }
}
