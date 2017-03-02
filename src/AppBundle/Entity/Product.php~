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
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\ProductRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="product")
 */
class Product
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
     * @ORM\Column(name="price", type="decimal", precision=7, scale=2, options={"default" : 0})
     * @Assert\NotBlank(message="Not null")
     * @Filter\StripTags()
     * @Filter\Trim()
     * @Filter\StripNewlines()
     */
    protected $price;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Category",
     *     inversedBy="products",
     *     cascade={"persist"}
     *)
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
    */
    protected $category;


    public function __construct()
    {
    }
}
