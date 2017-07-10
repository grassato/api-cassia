<?php

namespace AppBundle\Controller;

use AppBundle\Entity\DailyMenu;
use AppBundle\Manager\DailyMenuManager;
use BaseBundle\Api\ApiProblemException;
use BaseBundle\Controller\BaseController;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class DailyMenuController extends BaseController
{
    /**
     * @throws ApiProblemException
     * @ApiDoc(
     *  section="Api DailyMenu",
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
     * @FOSRest\Get("/dailymenus/{id}", name="_dailymenu")
     * @ParamConverter("DailyMenu", class="AppBundle:DailyMenu")
     * @FOSRest\View(serializerGroups={"identify", "dailymenu-details", "product-summary"})
     */
    public function getAction(DailyMenu $dailyMenu)
    {
        return $dailyMenu;
    }

    /**
     * @throws ApiProblemException
     *
     * @return array
     * @ApiDoc(
     *  section="Api DailyMenu",
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
     * @FOSRest\Get("/dailymenus", name="_dailymenus")
     * @FOSRest\View(serializerGroups={"identify", "dailymenu-details", "product-summary"})
     */
    public function cgetAction(Request $request)
    {
        $filtro['sort'] = $this->sanitizeDirectionFields($request->query->get('sort', ''));
        $qb = $this->getManager()->getRepo()->getQueryBuilder($filtro, false);

        $paginador = $this->get('api_pagination_factory');
        $collection = $paginador->createSimpleCollection($qb, $request);
        return $collection;
    }

    /**
     * This method should return the manager.
     *
     * @abstract
     *
     * @return DailyMenuManager
     */
    public function getManager()
    {
        return $this->get('dailymenu.manager');
    }

    /**
     * This method should return the manager.
     *
     * @abstract
     *
     * @return DailyMenu
     */
    protected function getEntityClass()
    {
        return DailyMenu::class;
    }

    /**
     * @throws ApiProblemException
     * Create a resource.
     *
     * @return array
     * @ApiDoc(
     *  section="Api DailyMenu",
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
     * @FOSRest\Post("/dailymenus", name="_dailymenus")
     * @FOSRest\View(serializerGroups={"identify", "dailymenu-details", "product-summary"})
     */
    public function postAction(Request $request)
    {
        $entityObject = $this->unserializeClass($request->getContent());


        $this->objectFilter($entityObject);
        $this->validate($entityObject);

        $this->getManager()->getAssociationTargetObject($entityObject);

        $menu = $this->getManager()->save($entityObject);

        return $menu;
    }

    /**
     * @throws ApiProblemException
     *
     * @return array
     * @ApiDoc(
     *  section="Api DailyMenu",
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
     * @FOSRest\Put("/dailymenus/{id}", name="_dailymenus")
     * @FOSRest\View(serializerGroups={"identify", "dailymenu-details", "product-summary"})
     */
    public function putAction($id, Request $request)
    {
        $entityObject = $this->unserializeClass($request->getContent());

        $this->objectFilter($entityObject);
        $this->validate($entityObject);

        $object = $this->getManager()->mergeObject($entityObject->toArray(), $id);

        $this->getManager()->getAssociationTargetObject($object);

        $this->getManager()->merge($object);

        return $object;
    }
}
