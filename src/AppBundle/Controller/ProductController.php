<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Post;

use AppBundle\Entity\Product;
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

class ProductController extends BaseController
{
    /**
     * @throws ApiProblemException
     * @ApiDoc(
     *  section="Api Product",
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
     * @FOSRest\Get("/products/{id}", name="_product")
     * @ParamConverter("product", class="AppBundle:Product")
     * @FOSRest\View(serializerGroups={"identify", "product-details", "category-summary"})
     */
    public function getAction(Product $product)
    {
        return $product;
    }

    /**
     * @throws ApiProblemException
     *
     * @return array
     * @ApiDoc(
     *  section="Api Product",
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
     * @FOSRest\Get("/products", name="_products")
     */
    public function cgetAction(Request $request)
    {
        $filtro['sort'] = $this->sanitizeDirectionFields($request->query->get('sort', ''));
        $qb = $this->getManager()->getRepo()->getQueryBuilder($filtro, false);

        $paginador = $this->get('api_pagination_factory');

        $collection = $paginador->createSimpleCollection($qb, $request);
        $groups = ['identify','product-summary', 'category-summary'];
        $response = $this->createApiResponse($collection, $groups);

        return $response;
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
        return $this->get('product.manager');
    }

    /**
     * This method should return the manager.
     *
     * @abstract
     *
     * @return ObjectManager
     */
    protected function getEntityClass()
    {
        return Product::class;
    }
}
