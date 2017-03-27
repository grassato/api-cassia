<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Comment;

use BaseBundle\Api\ApiProblemException;
use BaseBundle\Controller\BaseController;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class CommentController extends BaseController
{
    /**
     * @throws ApiProblemException
     * @ApiDoc(
     *  section="Api Demo",
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
     * @FOSRest\Get("/comment/{id}", name="_comment")
     */
    public function getAction($id)
    {
        $post = $this->getManager()->fetch($id);

        $comment = $this->getFractalData($post, $this->getTransformer());
        return $comment;
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
        return $this->get('app_api.transformer.comment');
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
     * @return Comment
     */
    protected function getEntityClass()
    {
        return Comment::class;
    }
}
