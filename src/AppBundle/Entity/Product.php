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
     * @Assert\NotBlank(message="product.not_null")
     * @Assert\Length(
     *     min = "5",
     *     minMessage = "product.too_short",
     *     max = "50",
     *     maxMessage = "product.too_long"
     * )
     * @Filter\StripTags()
     * @Filter\Trim()
     * @Filter\StripNewlines()
     * @Serializer\Groups({"product-details", "product-summary", "product-base"})
     */
    protected $name;

    /**
     * @ORM\Column(name="price", type="decimal", precision=7, scale=2, options={"default" : 0})
     * @Assert\NotBlank(message="product.not_null")
     * @Filter\StripTags()
     * @Filter\Trim()
     * @Filter\StripNewlines()
     * @Serializer\Groups({"product-details", "product-summary"})
     */
    protected $price;

    /**
     * @ORM\ManyToOne(targetEntity="Category", cascade={"persist"}, inversedBy="products")
     * @Serializer\Groups({"product-details", "product-summary"})
     */
    protected $category;

    public function __construct()
    {
          $this->dailyMenus = new ArrayCollection();
    }

    /**
     * @ORM\ManyToMany(targetEntity="DailyMenu", cascade={"persist"}, inversedBy="products")
     * @Serializer\Groups({"product-summary"})
     */
    protected $dailyMenus;

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

    /**
     * Add dailyMenu
     *
     * @param \AppBundle\Entity\DailyMenu $dailyMenu
     *
     * @return Product
     */
    public function addDailyMenu(\AppBundle\Entity\DailyMenu $dailyMenu)
    {
        $this->dailyMenus[] = $dailyMenu;

        return $this;
    }

    /**
     * Remove dailyMenu
     *
     * @param \AppBundle\Entity\DailyMenu $dailyMenu
     */
    public function removeDailyMenu(\AppBundle\Entity\DailyMenu $dailyMenu)
    {
        $this->dailyMenus->removeElement($dailyMenu);
    }

    /**
     * Get dailyMenus
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDailyMenus()
    {
        return $this->dailyMenus;
    }
}
