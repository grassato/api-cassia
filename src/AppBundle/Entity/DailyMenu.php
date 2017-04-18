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
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\DailyMenuRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="dailymenu")
 */
class DailyMenu
{
    use EntityTrait;

    /**
     * @ORM\Column(name="date", type="datetime")
     * @Assert\NotBlank(message="Not null")
     * @Assert\DateTime()
     * @Serializer\Type("DateTime<'Y-m-d h:m'>")
     * @Serializer\Groups({"dailymenu-summary", "dailymenu-details"})
     */
    protected $date;

    /**
     * @ORM\ManyToMany(targetEntity="Product", mappedBy="dailyMenus", cascade={"persist"})
     * @Serializer\Groups({"dailymenu-details"})
     */
    protected $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return DailyMenu
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Add product
     *
     * @param \AppBundle\Entity\Product $product
     *
     * @return DailyMenu
     */
    public function addProduct(\AppBundle\Entity\Product $product)
    {
        $this->products[] = $product;

        return $this;
    }

    /**
     * Add product
     *
     * @param \AppBundle\Entity\Product $product
     *
     * @return DailyMenu
     */
    public function setProducts(\AppBundle\Entity\Product $product = null)
    {
        $this->products = $product;

        return $this;
    }

    /**
     * Add products
     *
     * @param $products
     *
     * @return DailyMenu
     */
    public function addProducts($products)
    {
      foreach ($products as $product) {
        $this->addProduct($product);
      }
      return $this;
    }

    /**
     * Remove product
     *
     * @param \AppBundle\Entity\Product $product
     */
    public function removeProduct(\AppBundle\Entity\Product $product)
    {
        $this->products->removeElement($product);
    }

    /**
     * Get products
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProducts()
    {
        return $this->products;
    }
}
