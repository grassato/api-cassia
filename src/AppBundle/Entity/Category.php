<?php

namespace AppBundle\Entity;

use BaseBundle\Entity\EntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use DMS\Filter\Rules as Filter;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="category")
 */
class Category
{
    use EntityTrait;

    /**
     * @ORM\Column(name="name", type="string")
     * @Assert\NotBlank(message="Not null")
     * @Assert\Length(
     *     min = "5",
     *     minMessage = "post.too_short",
     *     max = "10000",
     *     maxMessage = "post.too_long"
     * )
     * @Filter\StripTags()
     * @Filter\Trim()
     * @Filter\StripNewlines()

     */
    protected $name;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Product",
     *     mappedBy="category",
     *     cascade={"persist"}
     *     )
     */
    protected $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    /**
     * @param \AppBundle\Entity\Product $product
     *
     * Check if product exits in this object
     * @return bool
     */
    public function hasProduct(\AppBundle\Entity\Product $product)
    {
        $test =
            function ($key, $element) use ($product) {
                if ($element->getId() === $product->getId()) {
                    return true;
                }

                return false;
            }
        ;

        return $this->products->exists($test);
    }
}
