<?php

namespace BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Hydrator\Reflection;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class AbstractEntity.
 *
 */
trait EntityTrait
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"identify"})
     */
    protected $id;

    /**
     * {@inheritdoc}
     */
    public function exchangeArray(array $array)
    {
        return (new Reflection())->hydrate($array, $this);
    }

    public function getArrayCopy()
    {
        return (new Reflection())->extract($this);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return (new Reflection())->extract($this);
    }


    /**
     * @var \DateTime
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     * @Assert\DateTime()
     * @Serializer\Groups({"timestamp"})
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(name="updated_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     * @Assert\DateTime()
     * @Serializer\Groups({"timestamp"})
     */
    protected $updatedAt;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Version
     */
    protected $version;

    /**
     * Metodo toString implementado com retorno do ID por causa das ações em massa.
     */
    public function __toString()
    {
        return $this->id ? (string) $this->id : '';
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        if ((int) $id <= 0) {
            throw new \RuntimeException(__FUNCTION__.' accept only positive integers greater than zero and');
        }
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $version
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return EntityTrait
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return EntityTrait
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
