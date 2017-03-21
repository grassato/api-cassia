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
     * @Serializer\Expose()
     */
    protected $name;

    /**
     * @ORM\Column(name="price", type="decimal", precision=7, scale=2, options={"default" : 0})
     * @Assert\NotBlank(message="Not null")Cart
     * @Filter\StripTags()
     * @Filter\Trim()
     * @Filter\StripNewlines()
     * @Serializer\Expose()
     */
    protected $price;

    /**
     * @ORM\ManyToOne(targetEntity="Category", cascade={"persist"}, inversedBy="products")
     * @Serializer\Expose()
     */
    protected $category;


    public function __construct()
    {
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Product
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set category
     *
     * @param \AppBundle\Entity\Category $category
     *
     * @return Product
     */
    public function setCategory(\AppBundle\Entity\Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \AppBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }
}
