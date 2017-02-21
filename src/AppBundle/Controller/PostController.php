<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Post;

use BaseBundle\Api\ApiProblemException;
use BaseBundle\Controller\BaseController;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

class PostController extends BaseController
{
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
     * @FOSRest\Put("/post/{id}", name="_post")
     */
    public function putAction($id, Request $request)
    {
        $entityObjet = $this->unserializeClass($request->getContent());

        $this->objectFilter($entityObjet);
        $this->validate($entityObjet);

        /**
         * @var $entity Post
         */
        $entity = $this->getManager()->fetch($id);

        $entity->setTitle($entityObjet->getTitle());
        $entity->setContent($entityObjet->getContent());
        $entity->setSlug($entityObjet->getSlug());

        $this->getManager()->merge($entity, $id);

        $post = $this->getFractalData($entity, $this->getTransformer(), 'comment');

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
     * @FOSRest\Post("/post", name="_post")
     */
    public function createAction(Request $request)
    {
        $entityObjet = $this->unserializeClass($request->getContent());

        $this->objectFilter($entityObjet);
        $this->validate($entityObjet);

        $post = $this->getManager()->save($entityObjet);

        $post = $this->getFractalData($post, $this->getTransformer(), 'comment');

        return $post;
    }

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
     * @FOSRest\Get("/post/{id}", name="_post")
     */
    public function getAction($id)
    {
        $post = $this->getManager()->fetch($id);

        $collection = $this->getFractalData($post, $this->getTransformer(), 'comment');
        return $collection;
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
     * @FOSRest\Get("/post", name="_posts")
     */
    public function cgetAction(Request $request)
    {
        $filtro['sort'] = $this->sanitizaCampoDeOrdenacao($request->query->get('sort', ''));
        $qb = $this->getManager()->getRepo()->getPostQueryBuilder($filtro, false);

        $paginador = $this->get('api_pagination_factory');
        $paginatedCollection = $paginador
            ->createORMCollection($qb, $request, 'get_posts', $this->getTransformer());

        $collection = $paginador->collection($paginatedCollection, 'posts', ['comment' ], ['dtux' => 'lindo']);

        $response = $this->createApiResponse($collection);

        return $response;
    }

    /**
     * This method should return the manager.
     *
     * @abstract
     *
     * @return ObjectManager
     */
    protected function getTransformer()
    {
        return $this->get('app_api.transformer.post');
    }


    /**
     * This method should return the manager.
     *
     * @abstract
     *
     * @return ObjectManager
     */
    public function getManager()
    {
        return $this->get('app.manager');
    }

    /**
     * This method should return the manager.
     *
     * @abstract
     *
     * @return ObjectManager
     */
    protected function getEntiyClass()
    {
        return Post::class;
    }
}
