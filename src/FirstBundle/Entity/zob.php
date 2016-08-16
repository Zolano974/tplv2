<?php

namespace FirstBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * zob
 *
 * @ORM\Table(name="zob")
 * @ORM\Entity(repositoryClass="FirstBundle\Repository\zobRepository")
 */
class zob
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="chien", type="string", length=255)
     */
    private $chien;

    /**
     * @var string
     *
     * @ORM\Column(name="zobby", type="string", length=255)
     */
    private $zobby;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return zob
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
     * Set address
     *
     * @param string $address
     * @return zob
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string 
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set chien
     *
     * @param string $chien
     * @return zob
     */
    public function setChien($chien)
    {
        $this->chien = $chien;

        return $this;
    }

    /**
     * Get chien
     *
     * @return string 
     */
    public function getChien()
    {
        return $this->chien;
    }

    /**
     * Set zobby
     *
     * @param string $zobby
     * @return zob
     */
    public function setZobby($zobby)
    {
        $this->zobby = $zobby;

        return $this;
    }

    /**
     * Get zobby
     *
     * @return string 
     */
    public function getZobby()
    {
        return $this->zobby;
    }
}
